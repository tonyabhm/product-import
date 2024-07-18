<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\ResourceModel\ImportLog;

use \Armada\ProductImport\Model\ImportLog;

/**
 * Import history collection
 *
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Link table name
     *
     * @var string
     */
    protected $_linkTable;

    /**
     * Define resource model and assign link table name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Armada\ProductImport\Model\ImportLog::class,
            \Armada\ProductImport\Model\ResourceModel\ImportLog::class
        );
    }
}
