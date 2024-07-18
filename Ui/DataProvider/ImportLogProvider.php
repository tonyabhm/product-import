<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Ui\DataProvider;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Backend\Model\Session;
use Armada\ProductImport\Model\ResourceModel\ImportLog\CollectionFactory;

/**
 * Class ImportLogProvider
 *
 */
class ImportLogProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product Import collection
     *
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * Backend session
     *
     * @var Session
     */
    protected $backendSession;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Session $backendSession
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Session $backendSession,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->backendSession = $backendSession;
    }

    /**
     * Return collection
     *
     * @return AbstractCollection
     */
    public function getCollection()
    {
        $historyId = $this->backendSession->getLogHistoryId();
        if($historyId){
            $this->collection->addFieldToFilter("history_id", array('eq' => "$historyId" ));
        }
        return $this->collection;
    }

}
