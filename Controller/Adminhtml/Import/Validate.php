<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\Layout;
use Armada\ProductImport\Block\Adminhtml\Import\Frame\Result;
use Armada\ProductImport\Controller\Adminhtml\Import as ImportController;
use Armada\ProductImport\Model\Import;

/**
 * Import validate controller action.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends ImportController implements HttpPostActionInterface
{
    /**
     * @var Import
     */
    private $import;

    /**
     * Validate uploaded files action
     *
     * @return Layout|Redirect|ResultInterface
     */
    public function execute(): Layout|Redirect|ResultInterface
    {
        $data = $this->getRequest()->getPostValue();

        /** @var Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        /** @var $resultBlock Result */
        $resultBlock = $resultLayout->getLayout()->getBlock('import.frame.result');
        //phpcs:disable Magento2.Security.Superglobal
        if ($data) {
            $import = $this->getImport()->setData($data);
            $resultBlock->addAction('show', 'import_validation_container');
            try {
                $import->uploadSource();
                $successMessage = __('File is valid! '. 'Please wait to start the import process. '. ' Make sure your cron job is running to import the products.');
                $resultBlock->addSuccess($successMessage, false);
                $resultBlock->addAction('refresh', 'product_import_grid');
            } catch (LocalizedException $e) {
                $resultBlock->addError(__($e->getMessage()));
            } catch (Exception $e) {
                $resultBlock->addError(__('Sorry, but the data is invalid or the file is not uploaded.'.$e->getMessage()));
            }

            $resultBlock->addAction('clear', 'import_file');

            return $resultLayout;
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $resultBlock->addError(__('The file was not uploaded.'));
            return $resultLayout;
        }
        $this->messageManager->addErrorMessage(__('Sorry, but the data is invalid or the file is not uploaded.'));
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('armada/import/product');
        return $resultRedirect;
    }

    /**
     * Provides import model.
     *
     * @return Import
     */
    private function getImport(): Import
    {
        if (!$this->import) {
            $this->import = $this->_objectManager->get(Import::class);
        }
        return $this->import;
    }

}
