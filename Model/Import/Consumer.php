<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Model\Import;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;
use Armada\ProductImport\Api\Data\ProductImportInterface;
use Armada\ProductImport\Model\HistoryFactory as ImportHistory;
use Armada\ProductImport\Model\ResourceModel\ImportLog\CollectionFactory as ImportLogCollection;
use Armada\ProductImport\Model\Import\Product\Update as UpdateProduct;
use Armada\ProductImport\Model\Import\Status as ImportStatus;
use Armada\ProductImport\Model\Import\Log\Status as LogStatus;
use Armada\ProductImport\Helper\Report as ReportHelper;

/**
 * Consumer for export message.
 */
class Consumer
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UpdateProduct
     */
    private $UpdateProduct;

    /**
     * @var ImportHistory
     */
    protected $importHistory;

    /**
     * @var ImportLogCollection
     */
    protected $importLogCollection;

    /**
     * Consumer constructor.
     * @param LoggerInterface $logger
     * @param UpdateProduct $UpdateProduct
     * @param ImportHistory $importHistory
     * @param ImportLogCollection $importLogCollection
     * @param ReportHelper $reportHelper
     */
    public function __construct(
        LoggerInterface $logger,
        UpdateProduct $UpdateProduct,
        ImportHistory $importHistory,
        ImportLogCollection $importLogCollection,
        ReportHelper $reportHelper
    ) {
        $this->logger = $logger;
        $this->UpdateProduct = $UpdateProduct;
        $this->importHistory = $importHistory;
        $this->importLogCollection = $importLogCollection;
        $this->reportHelper = $reportHelper;
    }

    /**
     * Consumer logic.
     *
     * @param ProductImportInterface $productData
     * @return void
     */
    public function process(ProductImportInterface $productData)
    {
        $message ="";
        $errorFile ="";
        try {
            $result = $this->UpdateProduct->updateproduct($productData);
            if(isset($result['error_file']) && $result['error_file']){
                $errorFile = $result['error_file'];
            }
        } catch (LocalizedException | FileSystemException $exception) {
            $message = 'Something went wrong while import process. ' . $exception->getMessage();
            $this->logger->critical($message);
        }
        
        $this->updateImportHistory($productData, $message, $errorFile);
    }


    /**
     * Get Import History
     */
    public function getImportHistory($historyId)
    {
        return $this->importHistory->create()->load($historyId);
    }

    /**
     * Get Import log collection 
     */
    public function getImportLogCollection($historyId="", $status="")
    {

        $collection = $this->importLogCollection->create();
        if($historyId!=""){
            $collection->addFieldToFilter('history_id',$historyId);
        }
        if($status!=""){
            $collection->addFieldToFilter('status',$status);
        }
        return $collection;
    }

    /**
     * Get Import log collection count
     */
    public function getImportLogCollectionCount($historyId="", $status="")
    {
        $collection = $this->getImportLogCollection($historyId, $status);
        return $collection->getSize();
    }

    /**
     * Update the history table if the history id exists
     */
    public function updateImportHistory(ProductImportInterface $productData, $message, $errorFile)
    {
        try{
            $status = ImportStatus::IMPORT_STATUS_FAILED;
            $totalRows = $productData->getTotalRows();
            $rowId = $productData->getRowId();
            $historyId = $productData->getHistoryId();

            if($rowId >= $totalRows && $historyId){
                $historyModel = $this->getImportHistory($historyId);
                if(!$historyModel){
                    return true;
                }

                $executionResult = $this->reportHelper->getExecutionTime($historyModel->getStartedAt());

                $totalRows = $historyModel->getTotalRows();
                $totalSuccess = $this->getImportLogCollectionCount($historyId, LogStatus::STATUS_SUCCESS);
                $totalFailed = $this->getImportLogCollectionCount($historyId, LogStatus::STATUS_FAILED);

                if($totalSuccess >0 ){
                    $status = ImportStatus::IMPORT_STATUS_SUCCESS;
                }

                $summary = '<p> '.__("Total Rows: ").$totalRows.'</p>';
                $summary .= '<p> '.__("Success: ").$totalSuccess.'</p>';
                $summary .= '<p> '.__("Failed: ").$totalFailed.'</p>';

                $historyModel->setStatus($status)
                    ->setSummary($summary)
                    ->setExecutionTime($executionResult)
                    ->setErrorFile($errorFile)
                    ->save();
            }

        } catch (LocalizedException $exception) {
            $this->logger->critical('Something went wrong while updating the import hitory data. ' . $exception->getMessage());
        }
    }
}
