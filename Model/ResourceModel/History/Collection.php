<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\ResourceModel\History;

use \Armada\ProductImport\Model\History;

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
            \Armada\ProductImport\Model\History::class,
            \Armada\ProductImport\Model\ResourceModel\History::class
        );
        $this->_linkTable = $this->getTable('admin_user');
    }

    /**
     * Init select
     *
     * @return \Armada\ProductImport\Model\ResourceModel\History\Collection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['link_table' => $this->_linkTable],
            'link_table.user_id = main_table.user_id',
            ['username']
        );

        return $this;
    }
}
