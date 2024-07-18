<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);

namespace Armada\ProductImport\Block\Adminhtml\Form;

use Magento\Backend\Block\Template;
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Model\AbstractModel;
/**
 * Block form after
 */
class After extends Template
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Get current operation
     *
     * @return AbstractModel
     */
    public function getOperation(): AbstractModel
    {
        return $this->_registry->registry('current_operation');
    }
}
