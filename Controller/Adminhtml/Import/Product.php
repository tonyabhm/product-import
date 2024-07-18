<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Controller\Adminhtml\Import;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\View\Result\Page;
use Armada\ProductImport\Controller\Adminhtml\Import as ImportController;
use Armada\ProductImport\Helper\Data;

class Product extends ImportController implements HttpGetActionInterface
{
    /**
     * Product action
     *
     * @return Page
     */
    public function execute(): Page
    {
        $this->messageManager->addNotice(
            $this->_objectManager->get(Data::class)->getMaxUploadSizeMessage()
        );

        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->setActiveMenu('Armada_ProductImport::import_product');
        $resultPage->getConfig()->getTitle()->prepend(__('Import Products'));
        $resultPage->addBreadcrumb(__('Import Products'), __('Import Products'));

        return $resultPage;
    }
}
