<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;

/**
 * Block form Before
 */
class Before extends Template
{

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ){
        parent::__construct($context, $data);
    }
}
