<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\ResourceModel;

/**
 * Class ImportLog
 *
 * @api
 * @since 100.0.2
 */
class ImportLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('armada_product_import_log', 'log_id');
    }
}
