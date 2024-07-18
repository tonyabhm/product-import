<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Controller\Adminhtml\History;

use Armada\ProductImport\Helper\Report;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Armada\ProductImport\Model\Import;

/**
 * Download history controller
 */
class Download extends Action implements HttpGetActionInterface
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Armada_ProductImport::import';

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    /**
     * Download backup action
     *
     * @return Redirect|ResponseInterface
     * @throws Exception
     */
    public function execute(): Redirect|ResponseInterface
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $fileName = basename($this->getRequest()->getParam('filename'));
        $fieldName = basename($this->getRequest()->getParam('field'));

        /** @var Report $reportHelper */
        $reportHelper = $this->_objectManager->get(Report::class);

        if (!$reportHelper->importFileExists($fileName, $fieldName)) {
            /** @var Redirect $resultRedirect */

            $this->messageManager->addError(__("File not found!"));

            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/import/product');
            return $resultRedirect;
        }

        return $this->fileFactory->create(
            $fileName,
            ['type' => 'filename', 'value' => $this->getReportDirectory($fieldName) . $fileName],
            DirectoryList::VAR_IMPORT_EXPORT,
            'application/octet-stream',
            $reportHelper->getReportSize($fileName, $fieldName)
        );
    }

    /**
     * Get Import directory
     *
     * @param $fieldName
     * @return string
     */
    public function getReportDirectory($fieldName): string
    {
        return match ($fieldName) {
            'error_file' => Import::ERROR_DIRECTORY,
            default => Import::WORKING_DIRECTORY,
        };
    }
}
