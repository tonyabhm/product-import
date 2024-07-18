<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;
/**
 * Adminhtml import history page content block
 *
 * @api
 * @since 100.0.2
 */
class History extends Container
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();
        $this->removeButton('add');
    }
}
