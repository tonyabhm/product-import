<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Model\Import;

use Armada\ProductImport\Api\Data\ProductImportInterface;

/**
 * Class product info implementation for ProductImportInterface.
 */
class ProductInfo implements ProductImportInterface
{
    /**
     * @var string
     */
    private $historyId;

    /**
     * @var int
     */
    private $rowId;

    /**
     * @var int
     */
    private $totalRows;

    /**
     * @var string
     */
    private $sku;

    /**
     * @var string
     */
    private $price;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $qty;

    /**
     * @var string
     */
    private $isInStock;


    /**
     * @inheritdoc
     */
    public function getHistoryId(): string
    {
        return $this->historyId;
    }

    /**
     * @inheritdoc
     */
    public function setHistoryId(string $historyId): void
    {
        $this->historyId = $historyId;
    }

    /**
     * @inheritdoc
     */
    public function getRowId(): int
    {
        return $this->rowId;
    }

    /**
     * @inheritdoc
     */
    public function setRowId(int $rowId): void
    {
        $this->rowId = $rowId;
    }

    /**
     * @inheritdoc
     */
    public function getTotalRows(): int
    {
        return $this->totalRows;
    }

    /**
     * @inheritdoc
     */
    public function setTotalRows(int $totalRows): void
    {
        $this->totalRows = $totalRows;
    }

    /**
     * @inheritdoc
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    /**
     * @inheritdoc
     */
    public function setSku($sku): void
    {
        $this->sku = $sku;
    }

    /**
     * @inheritdoc
     */
    public function getPrice(): string|null
    {
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @inheritdoc
     */
    public function getStatus(): string|null
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @inheritdoc
     */
    public function getQty(): string|null
    {
        return $this->qty;
    }

    /**
     * @inheritdoc
     */
    public function setQty($qty): void
    {
        $this->qty = $qty;
    }

    /**
     * @inheritdoc
     */
    public function getIsInStock(): string|null
    {
        return $this->isInStock;
    }

    /**
     * @inheritdoc
     */
    public function setIsInStock(string $isInStock): void
    {
        $this->isInStock = $isInStock;
    }

}
