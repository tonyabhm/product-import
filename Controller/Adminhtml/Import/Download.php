<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Controller\Adminhtml\Import;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Backend\App\Action;

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
     * Sample product import file name
     *
     */
    const SAMPLE_PRODUCT_IMPORT_FILE = 'product_import.csv';

    /**
     * Module name
     *
     */
    const MODULE_NAME = "Armada_ProductImport";

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var ReadFactory
     */
    private $readFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     * @param RawFactory $resultRawFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        RawFactory $resultRawFactory,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory
    ) {
        parent::__construct(
            $context
        );
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
    }

    /**
     * Download backup action
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {

        $sampleFile = $this->getSampleFilePath(self::SAMPLE_PRODUCT_IMPORT_FILE);


        return $this->fileFactory->create(
            self::SAMPLE_PRODUCT_IMPORT_FILE,
            $this->getFileContents($sampleFile),
            DirectoryList::VAR_IMPORT_EXPORT,
            'application/octet-stream',
            $this->getFileSize($sampleFile)
        );
    }

    /**
     * Get Import directory
     *
     * @param $fileName
     * @return string
     */
    public function getSampleFilePath($fileName): string
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE,  self::MODULE_NAME);
        return $moduleDir . '/Files/Sample/' . $fileName;
    }


    /**
     * Get Directory read
     *
     * @return ReadInterface
     */
    private function getDirectoryRead(): ReadInterface
    {
        $moduleDir = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, self::MODULE_NAME);
        return $this->readFactory->create($moduleDir);
    }

    /**
     * Retrieve sample file size
     *
     * @param string $filePath
     * @return int|null
     */
    public function getFileSize(string $filePath): ?int
    {
        $directoryRead = $this->getDirectoryRead();
        $filePath = $directoryRead->getRelativePath($filePath);
        return isset($directoryRead->stat($filePath)['size'])
            ? $directoryRead->stat($filePath)['size'] : null;
    }

    /**
     * Returns Content for the given file
     *
     * @param $filePath
     * @return string
     * @throws FileSystemException
     */
    public function getFileContents($filePath): string
    {
        $directoryRead = $this->getDirectoryRead();
        $filePath = $directoryRead->getRelativePath($filePath);
        return $directoryRead->readFile($filePath);
    }
}
