<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */

namespace Armada\ProductImport\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;
Use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File as IoFile;
Use Magento\Framework\File\Csv;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\File\Size as FileSize;

/**
 * ImportExport data helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends AbstractHelper
{

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $file;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csv;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var WriterInterface
     */
    protected $configWriter;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var DirectoryList
     */
    protected $dir;

    /**
     * @var IoFile
     */
    private $ioFile;

    /**#@+
     * XML path for config data
     */
    const XML_PATH_EXPORT_LOCAL_VALID_PATH = 'general/file/productimport_local_valid_paths';

    const XML_PATH_BUNCH_SIZE = 'general/file/bunch_size';

    /**#@-*/

    /**#@-*/
    protected $_fileSize;


    /**
     * @param Context $context
     * @param FileSize $fileSize
     * @param ScopeConfigInterface $scopeConfig
     * @param WriterInterface $configWriter
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param DirectoryList $dir
     * @param File $file
     * @param IoFile $ioFile
     * @param Csv $csv
     */
    public function __construct(
        Context $context,
        FileSize $fileSize,
        ScopeConfigInterface $scopeConfig,
        WriterInterface $configWriter,
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        DirectoryList $dir,
        File $file,
        IoFile $ioFile,
        Csv $csv
    ) {
        $this->_fileSize = $fileSize;
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->dir = $dir;
        $this->file = $file;
        $this->ioFile = $ioFile;
        $this->csv = $csv;

        parent::__construct(
            $context
        );
    }


    /**
     * Get maximum upload size message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = $this->_fileSize->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = __('Make sure your file isn\'t more than %1M.', $maxImageSize);
        } else {
            $message = __('We can\'t provide the upload settings right now.');
        }
        return $message;
    }

    /**
     * Get valid path masks to files for importing/exporting
     *
     * @return string[]
     */
    public function getLocalValidPaths()
    {
        $paths = $this->scopeConfig->getValue(
            self::XML_PATH_EXPORT_LOCAL_VALID_PATH,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $paths;
    }

    /**
     * Retrieve size of bunch (how many entities should be involved in one import iteration)
     *
     * @return int
     */
    public function getBunchSize()
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_BUNCH_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Get CSV data
     */
    public function getCsvData(string $csvFilePath, $fileName =""): bool|array
    {
        if(!$csvFilePath || !$fileName){
            return false;
        }
        try {
            $filePath = $csvFilePath.$fileName;
            if ($this->file->isExists($filePath)) {
                return $this->csv->getData($filePath);
            } else {
                return false;
            }
        } catch (\Exception  $e) {
            return false;
        }
    }

    /**
     * Delete unwanted CSV file
     */
    public function deleteCsvFile(string $csvFilePath): bool|array
    {
        if(!$csvFilePath ){
            return false;
        }
        try {
            if ($this->file->isExists($csvFilePath)) {
                return $this->file->deleteFile($csvFilePath);
            } else {
                return false;
            }
        } catch (\Exception  $e) {
            return false;
        }
    }
}
