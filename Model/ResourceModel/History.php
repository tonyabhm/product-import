<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\ResourceModel;

/**
 * Class History
 *
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('armada_product_import_history', 'history_id');
    }

    /**
     * Retrieve last inserted report id by user id
     *
     * @param string $userId
     * @return int $lastId
     */
    public function getLastInsertedId($userId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' DESC')
            ->where('user_id = ?', $userId)
            ->limit(1);

        return $connection->fetchOne($select);
    }
}
