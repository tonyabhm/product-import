<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Controller\Adminhtml\Import;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\Model\Session;
use Magento\Backend\App\Action\Context;
use Armada\ProductImport\Controller\Adminhtml\Import as ImportController;

class Log extends ImportController implements HttpGetActionInterface
{

    /**
     * @var Session
     */
    private $backendSession;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $backendSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $backendSession
    ) {
        parent::__construct($context);
        $this->backendSession = $backendSession;
    }


    /**
     * Import Log action
     *
     * @return Page
     */
    public function execute(): Page
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Armada_ProductImport::import_product');
        $resultPage->getConfig()->getTitle()->prepend(__('Product Import Log'));
        $resultPage->addBreadcrumb(__('Product Import Log'), __('Product Import Log'));

        $historyId = $this->getRequest()->getParam('history_id');
        $this->backendSession->setLogHistoryId($historyId);

        return $resultPage;
    }
}
