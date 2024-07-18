<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Api\Data;

/**
 * Product import interface
 */
interface ProductImportInterface
{
    /**
     * Return history id.
     *
     * @return string
     */
    public function getHistoryId(): string;

    /**
     * Set row id.
     *
     * @param string $historyId
     * @return void
     */
    public function setHistoryId(string $historyId): void;

    /**
     * Return row id.
     *
     * @return int
     */
    public function getRowId(): int;

    /**
     * Set row id.
     *
     * @param int $rowId
     * @return void
     */
    public function setRowId(int $rowId): void;

    /**
     * Return total rows.
     *
     * @return int
     */
    public function getTotalRows(): int;

    /**
     * Set row id.
     *
     * @param int $totalRows
     * @return void
     */
    public function setTotalRows(int $totalRows): void;

    /**
     * Return product SKU.
     *
     * @return string
     */
    public function getSku(): string;

    /**
     * Set product SKU.
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku): void;

    /**
     * Return product Price.
     *
     * @return string|null
     */
    public function getPrice(): string|null;

    /**
     * Set product Price.
     *
     * @param string $price
     * @return void
     */
    public function setPrice(string $price): void;

    /**
     * Return product Status.
     *
     * @return string|null
     */
    public function getStatus(): string|null;

    /**
     * Set product Status.
     *
     * @param string $status
     * @return void
     */
    public function setStatus(string $status): void;

    /**
     * Return product Stock.
     *
     * @return string|null
     */
    public function getQty(): string|null;

    /**
     * Set product Stock.
     *
     * @param string $qty
     * @return void
     */
    public function setQty(string $qty): void;

    /**
     * Return product Is in stock.
     *
     * @return string|null
     */
    public function getIsInStock(): string|null;

    /**
     * Set product Is in Stock.
     *
     * @param string $isInStock
     * @return void
     */
    public function setIsInStock(string $isInStock): void;

}
