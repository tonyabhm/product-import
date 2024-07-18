<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model;

use Psr\Log\LoggerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Armada\ProductImport\Model\Source\Upload;
use Armada\ProductImport\Model\HistoryFactory as ImportHistory;
use Armada\ProductImport\Helper\Data as DataHelper;
use Armada\ProductImport\Helper\Import as ImportHelper;
/**
 * Import model
 *
 */
class Import extends AbstractModel
{

    const IMPORT_STATUS_PENDING = 0;

    const IMPORT_STATUS_PROCESSING = 1;

    const IMPORT_STATUS_FAILED = 3;

    public const BEHAVIOR_APPEND = 'append';

    public const BEHAVIOR_ADD_UPDATE = 'add_update';

    public const BEHAVIOR_REPLACE = 'replace';

    public const BEHAVIOR_DELETE = 'delete';

    public const BEHAVIOR_CUSTOM = 'custom';

    /**
     * Working Directory
     */
    public const WORKING_DIRECTORY = 'productimport/';

    /**
     * Error Report Directory
     */
    public const ERROR_DIRECTORY = 'productimport/errors/';

    /**
     * Import file name.
     */
    public const IMPORT_FILE_NAME = 'product_import';

    /**
     * Import source file.
     */
    public const FIELD_NAME_SOURCE_FILE = 'import_file';

    /**
     * Id of the `productimport_importdata` row after validation.
     */
    public const FIELD_IMPORT_IDS = '_import_ids';

    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * @var ImportHelper
     */
    protected $importHelper;

    /**
     * @var Upload
     */
    private $upload;

    /**
     * @var ImportHistory
     */
    protected $importHistory;

    /**
     * Uploaded file
     */
    protected $uploadedFile = "";

    /**
     * @param LoggerInterface $logger
     * @param Filesystem $filesystem
     * @param DataHelper $dataHelper
     * @param ImportHelper $importHelper
     * @param ImportHistory $importHistory
     * @param array $data
     * @param Upload|null $upload
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LoggerInterface $logger,
        Filesystem $filesystem,
        DataHelper $dataHelper,
        ImportHelper $importHelper,
        ImportHistory $importHistory,
        array $data = [],
        Upload $upload = null,
    ) {
        $this->dataHelper = $dataHelper;
        $this->importHelper = $importHelper;
        $this->importHistory = $importHistory;
        $this->upload = $upload ?: ObjectManager::getInstance()
            ->get(Upload::class);
        $this->logger = $logger;
        parent::__construct($logger, $filesystem, $data);
    }

    /**
     * Get working directory path.
     *
     * @return string
     */
    public function getWorkingDir(): string
    {
        return $this->_varDirectory->getAbsolutePath(self::WORKING_DIRECTORY);
    }

    /**
     * get error report directory path
     *
     * @return string
     */
    public function getErrorReportDir(): string
    {
        return $this->_varDirectory->getAbsolutePath(self::ERROR_DIRECTORY);
    }

    /**
     * Move uploaded file.
     *
     * @throws LocalizedException
     * @return string Source file path
     */
    public function uploadSource(): string
    {
        // Check product import is enabled or not
        if(!$this->importHelper->isEnabled()){
            throw new LocalizedException(__("Product Import is not enabled."));
        }
        $fileName = self:: IMPORT_FILE_NAME."_".time();
        $result = $this->upload->uploadSource($fileName);
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $extension = pathinfo($result['file'], PATHINFO_EXTENSION);
        $fileName = $fileName.".".$extension;
        $sourceFile = $this->getWorkingDir() . $fileName;
        $this->uploadedFile = $sourceFile;
        $this->validateCsvFile($fileName);
        $this->_removeBom($sourceFile);
        $this->saveImportHistory($fileName);

        return $sourceFile;
    }

    /**
     * Throw error message and add log
     * Also remove unwanted CSV files from server
     *
     * @param $message
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function throwError($message): void
    {
        // Remove uploaded file
        if($this->uploadedFile){
            $this->dataHelper->deleteCsvFile($this->uploadedFile);
        }
        $this->logger->error("Invalid CSV file");
        throw new LocalizedException(__($message));
    }

    /**
     * Validate CSV File
     *
     * @param $fileName
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateCsvFile($fileName): void
    {
        $productFields = [];
        $csvFilePath = $this->getWorkingDir();
        $csvData = $this->dataHelper->getCsvData($csvFilePath, $fileName);
        if(!$csvData){
            $message = "Invalid CSV file";
            $this->throwError($message);
        }

        // Update import history data
        $totalRows = count($csvData)-1;

        foreach ($csvData as $key => $productData) {
            if($key == 0){
                foreach ($productData as $k => $fieldName) {
                    $productFields[] = $fieldName;
                }
                break;
            }
        }

        $this->checkAllowedFields($productFields);
    }

    /**
     * Check allowed fields
     *
     * @param $productFields
     * @return void
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkAllowedFields($productFields): void
    {
        $allowedFields = $this->importHelper->getAllowedProductAttributes();
        $requiredFields = [];
        $missingFields = [];
        $invalidFields = [];

        foreach ($allowedFields as $field => $required) {
            if($required){
                if(!in_array($field, $productFields)){
                    $missingFields[] = $field;
                }
            }
        }

        if(!empty($missingFields)){
            $message = "Please add the required field(s): ".implode(", ",$missingFields);
            $this->throwError($message);
        }

        foreach ($productFields as $key => $importField) {
            if(!in_array($importField, array_keys($allowedFields))){
                $invalidFields[] = $importField;
            }
        }

        if(!empty($invalidFields)){
            $message = "Please remove the invalid field(s): ".implode(", ",$invalidFields);
            $this->throwError($message);
        }
    }

    /**
     * Save import history data
     *
     * @param string $filename
     * @return History
     */
    public function saveImportHistory(string $filename): History
    {
        $historyModel = $this->importHistory->create();
        $adminId = $historyModel->getAdminId();

        $historyModel = $historyModel->setImportedFile($filename)
                                ->setUserId($adminId);
        $historyModel->save();
        return $historyModel;
    }

    /**
     * Remove BOM from a file
     *
     * @param string $sourceFile
     * @return $this
     * @throws FileSystemException
     */
    protected function _removeBom(string $sourceFile): static
    {
        $driver = $this->_varDirectory->getDriver();
        $string = $driver->fileGetContents($this->_varDirectory->getAbsolutePath($sourceFile));
        if ($string !== false && substr($string, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $string = substr($string, 3);
            $driver->filePutContents($this->_varDirectory->getAbsolutePath($sourceFile), $string);
        }
        return $this;
    }
}
