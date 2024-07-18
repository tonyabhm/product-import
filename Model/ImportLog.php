<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model;

/**
 * Import log model
 *
 */
class ImportLog extends \Magento\Framework\Model\AbstractModel
{
    const LOG_ID = 'log_id';

    const HISTORY_ID = 'history_id';

    const SKU = 'sku';

    const STATUS = 'status';

    const OLD_VALUE = 'old_value';

    const NEW_VALUE = 'new_value';

    const MESSAGE = 'message';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';


    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Armada\ProductImport\Model\ResourceModel\History $resource
     * @param \Armada\ProductImport\Model\ResourceModel\History\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Armada\ProductImport\Model\ResourceModel\ImportLog $resource,
        \Armada\ProductImport\Model\ResourceModel\ImportLog\Collection $resourceCollection,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize history resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Armada\ProductImport\Model\ResourceModel\ImportLog::class);
    }

    /**
     * Get import log ID
     *
     * @return string
     */
    public function getLogId()
    {
        return $this->getData(self::LOG_ID);
    }

    /**
     * Get import history ID
     *
     * @return string
     */
    public function getHistoryId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * Get import SKU
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    /**
     * Get import status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Get import old value
     *
     * @return string
     */
    public function getOldValue()
    {
        return $this->getData(self::OLD_VALUE);
    }

    /**
     * Get import new value
     *
     * @return string
     */
    public function getNewValue()
    {
        return $this->getData(self::NEW_VALUE);
    }

    /**
     * Get import history created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Get import history update at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Get import history report summary
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * Set history log ID
     *
     * @param int $logId
     * @return $this
     */
    public function setLogId($logId)
    {
        return $this->setData(self::HISTORY_ID, $logId);
    }

    /**
     * Set history id
     *
     * @param string $historyId
     * @return $this
     */
    public function setHistoryId($historyId)
    {
        return $this->setData(self::HISTORY_ID, $historyId);
    }

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::SKU, $sku);
    }

    /**
     * Set status
     *
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Set Old value
     *
     * @param string $oldValue
     * @return $this
     */
    public function setOldValue($oldValue)
    {
        return $this->setData(self::OLD_VALUE, $oldValue);
    }

    /**
     * Set message
     *
     * @param string $newValue
     * @return $this
     */
    public function setNewValue($newValue)
    {
        return $this->setData(self::NEW_VALUE, $newValue);
    }

    /**
     * Set message
     *
     * @param string $message
     * @return $this
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

}
