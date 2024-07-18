<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Block\Adminhtml\Import;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class Edit extends Container
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct(): void
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->update('save', 'label', __('Import Data'));
        $this->buttonList->update('save', 'id', 'upload_button');
        $this->buttonList->update('save', 'onclick', 'varienImport.postToFrame();');
        $this->buttonList->update('save', 'data_attribute', '');

        $this->_objectId = 'import_id';
        $this->_blockGroup = 'Armada_ProductImport';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * Get header text
     *
     * @return Phrase
     */
    public function getHeaderText(): Phrase
    {
        return __('Import');
    }
}
