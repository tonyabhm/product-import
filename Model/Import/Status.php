<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Model\Import;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve options array.
     *
     * @return array
     */

    const IMPORT_STATUS_PENDING = '0';

    const IMPORT_STATUS_PROCESSING = '1';

    const IMPORT_STATUS_SUCCESS = '2';

    const IMPORT_STATUS_FAILED = '3';

    public static function getOptionArray()
    {
        return [
            self::IMPORT_STATUS_PENDING => __('Pending'),
            self::IMPORT_STATUS_PROCESSING => __('In Progress'),
            self::IMPORT_STATUS_SUCCESS => __('Completed'),
            self::IMPORT_STATUS_FAILED => __('Failed')
        ];
    }

    public function toOptionArray()
    {
        $result = [];
        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }
}

?>