<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */

namespace Armada\ProductImport\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Armada\ProductImport\Model\Import;
use Magento\Framework\Stdlib\DateTime\Timezone;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * ImportExport history reports helper
 *
 * @api
 * @since 100.0.2
 */
class Report extends AbstractHelper
{
    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Timezone
     */
    protected $timeZone;

    /**
     * @var WriteInterface
     */
    protected $varDirectory;

    /**
     * @var ReadInterface
     */
    private $importHistoryDirectory;

    /**
     * @var ReadInterface
     */
    private $importErrorDirectory;

    /**
     * @var WriteInterface
     */
    private WriteInterface $directory;

    /**
     * Construct
     *
     * @param Context $context
     * @param Timezone $timeZone
     * @param Filesystem $filesystem
     * @param DateTime $date
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        TimezoneInterface $timeZone,
        DateTime $date,
        Filesystem $filesystem
    ) {
        $this->timeZone = $timeZone;
        $this->date = $date;
        $this->varDirectory = $filesystem->getDirectoryWrite(DirectoryList::VAR_IMPORT_EXPORT);
        $workingDirectory = $this->varDirectory->getAbsolutePath(Import::WORKING_DIRECTORY);
        $this->importHistoryDirectory = $filesystem->getDirectoryReadByPath($workingDirectory);
        $errorDirectory = $this->varDirectory->getAbsolutePath(Import::ERROR_DIRECTORY);
        $this->importErrorDirectory = $filesystem->getDirectoryReadByPath($errorDirectory);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    /**
     * Calculate import time
     *
     * @param string $time
     * @return string
     */
    public function getExecutionTime(string $time): string
    {
        $date = $this->date->gmtDate();
        $reportTime = $this->timeZone->date($time);
        $currentTime = $this->timeZone->date($date);
        $timeDiff = $reportTime->diff($currentTime);
        return $timeDiff->format('%H:%I:%S');
    }

    /**
     * Get import summary
     *
     * @param Import $import
     * @return string
     */
    public function getSummaryStats(Import $import): string
    {
        return __(
            'Created: %1, Updated: %2, Deleted: %3',
            $import->getCreatedItemsCount(),
            $import->getUpdatedItemsCount(),
            $import->getDeletedItemsCount()
        );
    }

    /**
     * Checks imported file exists.
     *
     * @param string $filename
     * @param $fieldName
     * @return bool
     */
    public function importFileExists(string $filename, $fieldName): bool
    {
        return $this->varDirectory->isFile($this->getFilePath($filename, $fieldName));
    }



    /**
     * Retrieve report file size
     *
     * @param string $filename
     * @param $fieldName
     * @return int|null
     */
    public function getReportSize(string $filename, $fieldName): ?int
    {
        $statResult = $this->varDirectory->stat($this->getFilePath($filename, $fieldName));

        return $statResult['size'] ?? null;
    }

    /**
     * Get absolute path based on field name
     *
     * @param string $filename
     * @param $fieldName
     * @return string
     */
    protected function getAbsolutePath(string $filename, $fieldName): string
    {
        return match ($fieldName) {
            'error_file' => $this->importErrorDirectory->getAbsolutePath($filename),
            default => $this->importHistoryDirectory->getAbsolutePath($filename),
        };
    }

    /**
     * Get file path.
     *
     * @param string $filename
     * @param $fieldName
     * @return string
     */
    protected function getFilePath(string $filename, $fieldName): string
    {
        try {
            $filePath = $this->varDirectory->getRelativePath($this->getAbsolutePath($filename, $fieldName));
        } catch (ValidatorException $e) {
            throw new \InvalidArgumentException('File not found');
        }
        return $filePath;
    }

    public function setErrorReport($fileName, $header = [], $data =[]): void
    {
        $filePath = $this->getAbsolutePath($fileName, 'error_file');
        $this->directory->create(Import::ERROR_DIRECTORY);

        $addHeader = false;
        if(!$this->importFileExists($fileName, 'error_file')){
            $addHeader = true;
        }

        $stream = $this->directory->openFile($filePath, 'a+');
        $stream->lock();

        if($addHeader){
            $stream->writeCsv($header);
        }

        if(!empty($data)){
            $stream->writeCsv($data);
        }
    }
}
