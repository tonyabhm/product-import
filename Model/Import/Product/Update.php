<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\Import\Product;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Interceptor;
use Magento\Catalog\Model\Product\Action as ProductAction;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Psr\Log\LoggerInterface;
use Armada\ProductImport\Api\Data\ProductImportInterface;
use Armada\ProductImport\Model\ImportLogFactory as ImportLog;
use Armada\ProductImport\Model\Import\Log\Status as LogStatus;
use Armada\ProductImport\Helper\Import as ImportHelper;
use Armada\ProductImport\Helper\Report as ReportHelper;

/**
 * Product Update model
 *
 */
class Update
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductAction
     */
    protected $productAction;

    /**
     * @var StockStateInterface
     */
    protected $stockStateInterface;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ImportLog
     */
    protected $importLog;

    /**
     * @var ImportHelper
     */
    protected $importHelper;

    /**
     * @var ReportHelper
     */
    protected $reportHelper;

    /**
     * Attributes list
     */
    protected $allowedAttributes = [
        'price',
        'status',
        'qty',
        'is_in_stock'
    ];

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAction $productAction
     * @param StockStateInterface $stockStateInterface
     * @param StockRegistryInterface $stockRegistry
     * @param Attribute $eavAttribute
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param ImportLog $importLog
     * @param ImportHelper $importHelper
     * @param ReportHelper $reportHelper
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAction $productAction,
        StockStateInterface $stockStateInterface,
        StockRegistryInterface $stockRegistry,
        Attribute $eavAttribute,
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        ImportLog $importLog,
        ImportHelper $importHelper,
        ReportHelper $reportHelper
    ) {
        $this->productRepository = $productRepository;
        $this->productAction = $productAction;
        $this->stockStateInterface = $stockStateInterface;
        $this->stockRegistry = $stockRegistry;
        $this->eavAttribute = $eavAttribute;
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->importLog = $importLog;
        $this->importHelper = $importHelper;
        $this->reportHelper = $reportHelper;
    }


    /**
     * Get product
     */
    public function getProduct(string $sku)
    {
        try {
            $product = $this->productRepository->get($sku);
        } catch (NoSuchEntityException $exception) {
            $product = false;
        }

        return $product;
    }

    /**
     * Add import log
     */
    public function setImportLog(ProductImportInterface $productData, array $oldData, $status, $message): void
    {
        try {
            $sku = $productData->getSku();
            $historyId = $productData->getHistoryId();

            $newData = $this->getNewDataFromImport($productData);

            $logModel = $this->importLog->create();
            $update = $logModel->setSku($sku)
                ->setHistoryId($historyId)
                ->setOldData(json_encode($oldData))
                ->setNewData(json_encode($newData))
                ->setStatus($status)
                ->setMessage($message)
                ->save();
        }catch (Exception | LocalizedException $exception) {
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * Add import log
     */
    public function setErrorReport(ProductImportInterface $productData, $message=""): string
    {
        $fileName = "";
        $errorData = $this->getNewDataFromImport($productData);
        try {
            $historyId = $productData->getHistoryId();
            $fileName = md5($historyId).".csv";
            $errorData['message'] = $message;
            $header = array_keys($errorData);
            $data = array_values($errorData);
            $this->reportHelper->setErrorReport($fileName, $header, $data);
        }catch (Exception | LocalizedException $exception) {
            $this->logger->critical($message);
        }
        return $fileName;
    }

    /**
     * Get Old data from product
     */
    public function getOldDataFromProduct(Interceptor $product): array
    {
        $data = [];
        foreach ($this->allowedAttributes as $attribute) {
            $data[$attribute] = $product->getData($attribute);
        }
        return $data;
    }

    /**
     * Get new data from import
     */
    public function getNewDataFromImport(ProductImportInterface $productData): array
    {
        $data = [];
        $data["sku"] = $productData->getSku();
        $data["price"] = $productData->getPrice();
        $data["status"] = $productData->getStatus();
        $data["qty"] = $productData->getQty();
        $data["is_in_stock"] = $productData->getIsInStock();

        return $data;
    }

    /**
     * Update product
     */
    public function updateproduct(ProductImportInterface $productData): array
    {
        $sku = $productData->getSku();
        $product = $this->getProduct($sku);
        $message = "";
        $status = LogStatus::STATUS_FAILED;
        $oldData = [];
        $result = [];

        try{
            if($product){
                $productId = $product->getId();

                // Set old data before updating
                $oldData = $this->getOldDataFromProduct($product);

                $attributes = [];

                if($productData->getPrice() != ""){
                    $attributes['price'] = $productData->getPrice();
                }

                if($productData->getStatus() != ""){
                    $status = $productData->getStatus()==ProductStatus::STATUS_ENABLED?ProductStatus::STATUS_ENABLED:ProductStatus::STATUS_DISABLED;
                    $attributes['status'] = $status;
                }

                if(!empty($attributes)){
                    $this->productAction->updateAttributes([$productId], $attributes, 0);
                    $this->removeStoreSpecificData($attributes, $productId);
                }

                // Update product stock
                $this->updateProductStock($productId, $productData);
                $status = LogStatus::STATUS_SUCCESS;
            }else{
                $message = "The product that was requested doesn't exist. Verify the product and try again.";
                $this->logger->error($message);
            }

        } catch (Exception | LocalizedException $exception) {
            $message = $exception->getMessage();
            $this->logger->critical($message);
        }

        $setLog = $this->setImportLog($productData, $oldData, $status, $message);

        // Create error report file
        $result['status'] = $status;
        if($status == LogStatus::STATUS_FAILED){
            $result['error_file'] =  $this->setErrorReport($productData, $message);
        }

        return $result;
    }

    /**
     * Get attribute id by code
     */
    public function getAttributeidByCode(string $attributeCode): int
    {
        return $this->eavAttribute->getIdByCode(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode
        );
    }

    /**
     * Get Table name using direct query
     */
    public function getTablename($tableName): string
    {
        $connection  = $this->resourceConnection->getConnection();
        return $connection->getTableName($tableName);
    }

    /**
     * Run direct query
     */
    public function runQuery($query): bool
    {
        try{
            $connection = $this->resourceConnection->getConnection();
            $connection->query($query);
        } catch (Exception | LocalizedException $exception) {
            $message = $exception->getMessage();
            $this->logger->critical($message);
        }
        return true;
    }

    /**
     * Generate query
     */
    public function generateDeleteQuery($table, $productId, $attributeId): string
    {
        $query = "DELETE FROM `".$table."` where entity_id = ".$productId." AND attribute_id = ".$attributeId." AND store_id != 0;";
        return $query;
    }

    /**
     * If the product status or prices are uploaded for the default store,
     * any store-specific values for those products should be removed.
     */
    public function removeStoreSpecificData(array $attributes, $productId)
    {

        if(isset($attributes['price'])){
            $attributeId = $this->getAttributeidByCode('price');
            $table = $this->getTablename("catalog_product_entity_decimal");
            $query = $this->generateDeleteQuery($table, $productId, $attributeId);
            $this->runQuery($query);
        }

        if(isset($attributes['status'])){
            $attributeId = $this->getAttributeidByCode('status');
            $table = $this->getTablename("catalog_product_entity_int");
            $query = $this->generateDeleteQuery($table, $productId, $attributeId);
            $this->runQuery($query);
        }
    }


    /**
     * For Update stock of product
     * @param int $productId which stock you want to update
     * @param ProductImportInterface $productData your updated data
     * @return void
    */
    public function updateProductStock($productId, ProductImportInterface $productData) {
        $stockItem=$this->stockRegistry->getStockItem($productId);
        $qty = intval($productData->getQty());
        if($qty >= 0 ){
            $isInStock = $productData->getIsInStock();
            if($isInStock == ""){
                $isInStock = $productData->getQty() >0 ? 1:0;
            }
            $stockItem->setData('is_in_stock', $isInStock);
            $stockItem->setData('qty', $productData->getQty());
            $stockItem->setData('manage_stock', 1);
            $stockItem->setData('use_config_notify_stock_qty',1);
            $stockItem->save();
        }
    }
}

