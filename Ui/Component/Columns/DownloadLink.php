<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Ui\Component\Columns;

use Magento\Framework\Escaper;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Armada\ProductImport\Controller\Adminhtml\Export\File\Download;
use Armada\ProductImport\Controller\Adminhtml\Export\File\Delete;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Download link.
 */
class DownloadLink extends Column
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Escaper $escaper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ?Escaper $escaper = null,
        ?UrlInterface $backendUrl = null,
        array $components = [],
        array $data = []
    ) {
        $this->escaper = $escaper
            ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->backendUrl = $backendUrl
            ?? ObjectManager::getInstance()->get(UrlInterface::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource){
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if($item[$fieldName]){
                    $fileName = $item[$fieldName];
                    $item[$fieldName] = '<a href="'.$this->getDownloadFileUrl($fileName, $fieldName).'" target="_blank">'.__('Download').'</a>';
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get Import History Url
     *
     * @param string $fileName
     * @return string
     */
    public function getDownloadFileUrl(string $fileName, string $field): string
    {
        return $this->backendUrl->getUrl('armada/history/download', ['filename' => $fileName,'field' => $field]);
    }

}
