<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Cron;

use Exception;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\MessageQueue\PublisherInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;
use Armada\ProductImport\Model\History as ImportHistory;
use Armada\ProductImport\Model\Import as ProductImport;
use Armada\ProductImport\Model\Import\ProductInfo;
use Armada\ProductImport\Model\Import\ProductInfoFactory;
use Armada\ProductImport\Helper\Data as DataHelper;
use Armada\ProductImport\Helper\Import as ImportHelper;

class ImportProductData
{
    /**
     * @var PublisherInterface
     */
    private $messagePublisher;

    /**
     * @var TimezoneInterface
     */
    private $timezoneInterface;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Escaper|mixed
     */
    private mixed $escaper;

    /**
     * @var ImportHistory
     */
    protected $importHistory;

    /**
     * @var ProductImport
     */
    protected $productImport;

    /**
     * @var ProductInfoFactory
     */
    protected $productInfo;

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var ImportHelper
     */
    protected $importHelper;

    /**
     * @param StoreManagerInterface $storeManagerInterface
     * @param TimezoneInterface $timezoneInterface
     * @param DateTime $date
     * @param LoggerInterface $logger
     * @param ImportHistory $importHistory
     * @param ProductImport $productImport
     * @param ProductInfoFactory $productInfo
     * @param DataHelper $dataHelper
     * @param ImportHelper $importHelper
     * @param Escaper|null $escaper
     * @param PublisherInterface|null $publisher
     *
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezoneInterface,
        DateTime $date,
        LoggerInterface $logger,
        ImportHistory $importHistory,
        ProductImport $productImport,
        ProductInfoFactory $productInfo,
        DataHelper $dataHelper,
        ImportHelper $importHelper,
        ?Escaper $escaper = null,
        PublisherInterface $publisher = null
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->timezoneInterface = $timezoneInterface;
        $this->date = $date;
        $this->logger = $logger;
        $this->importHistory = $importHistory;
        $this->productImport = $productImport;
        $this->productInfo = $productInfo;
        $this->dataHelper = $dataHelper;
        $this->importHelper = $importHelper;

        $this->messagePublisher = $publisher
            ?? ObjectManager::getInstance()->get(PublisherInterface::class);
        $this->escaper = $escaper
            ?? ObjectManager::getInstance()->get(Escaper::class);
    }

    /**
     * Remove all rows from productimport_importdata table
     *
     * @return bool|void
     */
    public function execute()
    {
        // Check product import is enabled or not
        if(!$this->importHelper->isEnabled()){
            return true;
        }

        try {
            // Import pending products data
            return $this->importProduct();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Check for pending import files and import the product data
     *
     * @return bool|void
     */
    public function importProduct(){
        try {

            $csvFilePath = $this->productImport->getWorkingDir();
            $collection = $this->importHistory->getCollection();
            $collection->addFieldToFilter('status', ProductImport::IMPORT_STATUS_PENDING);
            if (!$collection->getSize()) {
                return true;
            }
            $importData = $collection->getFirstItem();

            if (!$importData) {
                return true;
            }

            $fileName = $importData->getImportedFile();
            $csvData = $this->dataHelper->getCsvData($csvFilePath, $fileName);
            if (!$csvData) {
                $message = "Invalid CSV file.";
                $this->logger->error($message . " Import history Id: " . $importData->getHistoryId());
                $this->updateHistoryData(
                    $importData,
                    [
                        'status' => ProductImport::IMPORT_STATUS_FAILED,
                        'summary' => $message
                    ]
                );
                return false;
            }

            // Update import history data
            $totalRows = count($csvData) - 1;
            $this->updateHistoryData(
                $importData,
                [
                    'status' => ProductImport::IMPORT_STATUS_PROCESSING,
                    'total_rows' => $totalRows,
                    'started_at' => $this->getStartTime()
                ]
            );

            foreach ($csvData as $key => $productData) {
                if ($key == 0) {
                    foreach ($productData as $k => $fieldName) {
                        $this->productFields[$fieldName] = $k;
                    }
                    continue;
                }
                $productData['history_id'] = $importData->getHistoryId();
                $productData['row_id'] = $key;
                $productData['total_rows'] = $totalRows;
                $dataObject = $this->prepareProductData($productData);
                $update = $this->updateProduct($dataObject);
                if (!$update) {
                    $this->logger->error("Job terminated due to error");
                    break;
                }
            }
        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * update History data
     *
     * @param ImportHistory $historyData
     * @param array $data
     * @return void
     * @throws Exception
     */
    public function updateHistoryData(ImportHistory $historyData, array $data): void
    {
        try {
            if(!empty($data)){
                foreach ($data as $key => $value) {
                    $historyData->setData($key, $value);
                }
                $historyData->save();
            }
        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Product info
     *
     * @param array $productData
     * @return ProductInfo
     */
    public function prepareProductData(array $productData): ProductInfo
    {
        $historyId = $productData['history_id']??"";
        $rowId = $productData['row_id']??"";
        $totalRows = $productData['total_rows']??"0";
        $skuKey = $this->productFields['sku']??"";
        $sku = $productData[$skuKey]??"";
        $priceKey = $this->productFields['price']??"";
        $price = $productData[$priceKey]??"";
        $statusKey = $this->productFields['status']??"";
        $status = $productData[$statusKey]??"";
        $qtyKey = $this->productFields['qty']??"";
        $qty = $productData[$qtyKey]??"";
        $isInStockKey = $this->productFields['is_in_stock']??"";
        $isInStock = $productData[$isInStockKey]??"";

        $dataObject = $this->productInfo->create();
        $dataObject->setHistoryId($historyId);
        $dataObject->setRowId($rowId);
        $dataObject->setTotalRows($totalRows);
        $dataObject->setSku($this->escaper->escapeHtml($sku));
        $dataObject->setPrice($this->escaper->escapeHtml($price));
        $dataObject->setStatus($this->escaper->escapeHtml($status));
        $dataObject->setQty($this->escaper->escapeHtml($qty));
        $dataObject->setIsInStock($this->escaper->escapeHtml($isInStock));

        return $dataObject;
    }

    /**
     * Publish product info to the message queue
     *
     * @param ProductInfo $productData
     * @return bool|null
     */
    public function updateProduct(ProductInfo $productData): ?bool
    {
        try {
            $this->messagePublisher->publish('productimport.import', $productData);
            return true;
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return false;
    }

    public function getStartTime(): string
    {
        return $this->date->gmtDate();
    }
}
