<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\View\Asset\Repository;
use Armada\ProductImport\Model\Import;
use Magento\Backend\Block\Widget\Form\Generic;

/**
 * Import edit form block
 */
class Form extends Generic
{

    /**
     * @var Repository
     */
    protected $_assetRepo;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Repository $assetRepo
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Repository $assetRepo,
        array $data = [],
    ) {
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Add field sets
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @throws LocalizedException
     */
    protected function _prepareForm(): static
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getUrl('armada/*/validate'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );

        // fieldset for file uploading
        $fieldset = $form->addFieldset(
            'upload_file_fieldset',
            ['legend' => __('File to Import'),]
        );
        $fieldset->addField(
            Import::FIELD_NAME_SOURCE_FILE,
            'file',
            [
                'name' => Import::FIELD_NAME_SOURCE_FILE,
                'label' => __('Select CSV File to Import'),
                'title' => __('Select File to Import'),
                'required' => true,
                'class' => 'input-file',
                'onchange' => 'varienImport.refreshLoadedFileLastModified(this);',
                'note' => __(
                    'Only CSV files are allowed. '.
                    'File must be saved in UTF-8 encoding for proper import'
                ),
                'after_element_html' => $this->getDownloadSampleFileHtml(),
            ]
        );

        $fieldset->addField(
            Import::FIELD_IMPORT_IDS,
            'hidden',
            [
                'name' => Import::FIELD_IMPORT_IDS,
                'label' => __('Import id'),
                'title' => __('Import id'),
                'value' => '',
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get download sample file html
     *
     * @return string
     */
    protected function getDownloadSampleFileHtml(): string
    {
        $url = $this->getSampleFileDownloadUrl();
        return '<span id="sample-file-span"><a id="sample-file-link" href="'.$url.'" target="_blank">'
            . __('Download Sample File')
            . '</a></span>';
    }

    /**
     * Get sample file download url
     *
     * @return string
     */
    public function getSampleFileDownloadUrl(): string
    {
        return $this->getUrl('armada/import/download');
    }

}
