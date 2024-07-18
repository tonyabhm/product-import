<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Model\Source;

use Laminas\File\Transfer\Adapter\Http;
use Laminas\Validator\File\Upload as FileUploadValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\HTTP\Adapter\FileTransferFactory;
use Magento\Framework\Math\Random;
use Armada\ProductImport\Helper\Data as DataHelper;
use Armada\ProductImport\Model\Import;
use Magento\MediaStorage\Model\File\Uploader;
use Magento\MediaStorage\Model\File\UploaderFactory;

class Upload
{
    /**
     * @var FileTransferFactory
     */
    private $httpFactory;

    /**
     * @var DataHelper
     */
    private $importExportData = null;

    /**
     * @var UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var WriteInterface
     */
    private $varDirectory;

    /**
     * @param FileTransferFactory $httpFactory
     * @param DataHelper $importExportData
     * @param UploaderFactory $uploaderFactory
     * @param Random|null $random
     * @param Filesystem $filesystem
     */
    public function __construct(
        FileTransferFactory $httpFactory,
        DataHelper $importExportData,
        UploaderFactory $uploaderFactory,
        Random $random,
        Filesystem $filesystem
    ) {
        $this->httpFactory = $httpFactory;
        $this->importExportData = $importExportData;
        $this->uploaderFactory = $uploaderFactory;
        $this->random = $random;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
    }
    /**
     * Move uploaded file.
     *
     * @param string $entity
     * @throws LocalizedException
     * @return array
     */
    public function uploadSource(string $fileName)
    {
        /**
         * @var $adapter Http
         */
        $adapter = $this->httpFactory->create();
        if (!$adapter->isValid(Import::FIELD_NAME_SOURCE_FILE)) {
            $errors = $adapter->getErrors();
            if ($errors[0] == FileUploadValidator::INI_SIZE) {
                $errorMessage = $this->importExportData->getMaxUploadSizeMessage();
            } else {
                $errorMessage = __('The file was not uploaded.');
            }
            throw new LocalizedException($errorMessage);
        }

        /**
         * @var $uploader Uploader
         */
        $uploader = $this->uploaderFactory->create(['fileId' => Import::FIELD_NAME_SOURCE_FILE]);
        $uploader->setAllowedExtensions(['csv']);
        $uploader->skipDbProcessing(true);
        $fileName = $fileName . '.' . $uploader->getFileExtension();
        try {
            $result = $uploader->save($this->varDirectory->getAbsolutePath('productimport/'), $fileName);
        } catch (\Exception $e) {
            throw new LocalizedException(__('The file cannot be uploaded. '.'Please make dure that you are uploading a valid CSV file.'));
        }

        return $result;
    }
}
