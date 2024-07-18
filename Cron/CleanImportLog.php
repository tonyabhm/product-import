<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Cron;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;
use Armada\ProductImport\Helper\Import as ImportHelper;
use Armada\ProductImport\Model\ImportLog;


class CleanImportLog
{

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
    * @var ImportHelper
    */
    protected $importHelper;

    /**
     * @var ImportLog
     */
    protected $importLog;

    /**
     * Constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param LoggerInterface $logger
     * @param ImportHelper $importHelper
     * @param ImportLog $importLog
     */

    public function __construct(
        ResourceConnection $resourceConnection,
        LoggerInterface $logger,
        ImportHelper $importHelper,
        ImportLog $importLog
    ){
        $this->resourceConnection = $resourceConnection;
        $this->logger = $logger;
        $this->importHelper = $importHelper;
        $this->importLog = $importLog;
    }

    /**
     * clean import logs
     *
     * @return bool|void
     */
    public function execute()
    {
        // Check product import is enabled or not
        if(!$this->importHelper->isEnabled()){
            return true;
        }

        try{
            $logIds = $this->getLogIdsToClean();
            $this->cleanImportLogTableByIds($logIds);

        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * Get impot ids to clean
     *
    */
    public function getLogIdsToClean(): array
    {
        $days=$this->getCleanupDaysString();
        $toDate = date("Y-m-d", strtotime($days));
        $collection = $this->importLog->getCollection();
        $collection->addFieldToSelect('job_id')
            ->addFieldToFilter('updated_at',array('lteq'=>$toDate));
        return $collection->getAllIds();
    }

    /**
     * Clean the import log table by import ids
     *
    */
    public function cleanImportLogTableByIds($logIds): bool
    {
        $deleted = false;
        if(empty($logIds)){
            return true;
        }
        try{
            $connection  = $this->resourceConnection->getConnection();
            $table = $connection->getTableName("armada_product_import_log");
            $this->resourceConnection->getConnection()->delete($table, ['log_id in (?)' => $logIds]);
            $deleted = true;
        }catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $deleted;
    }

    /**
     * Get clean after days
     *
    */
    public function getCleanupDaysString(): string
    {
        $days = $this->importHelper->getLogCleanupDays();
        $days= -1 * max(1,intval($days));
        return "$days day";
    }
}

