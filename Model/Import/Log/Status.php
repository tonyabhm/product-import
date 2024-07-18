<?php
namespace Armada\ProductImport\Model\Import\Log;

class Status implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Retrieve options array.
     *
     * @return array
     */

    const STATUS_SUCCESS = '1';

    const STATUS_FAILED = '0';

    public static function getOptionArray()
    {
        return [
            self::STATUS_SUCCESS => __('Success'),
            self::STATUS_FAILED => __('Failed')
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