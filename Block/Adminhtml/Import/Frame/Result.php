<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
declare(strict_types=1);
namespace Armada\ProductImport\Block\Adminhtml\Import\Frame;
use Magento\Backend\Block\Template;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Block\Template\Context;
/**
 * Import frame result block.
 */
class Result extends Template
{
    /**
     * JavaScript actions for response.
     *     'clear'           remove element from DOM
     *     'innerHTML'       set innerHTML property (use: elementID => new content)
     *     'value'           set value for form element (use: elementID => new value)
     *     'show'            show specified element
     *     'hide'            hide specified element
     *     'removeClassName' remove specified class name from element
     *     'addClassName'    add specified class name to element
     *
     * @var array
     */
    protected array $_actions = [
        'clear' => [],
        'innerHTML' => [],
        'value' => [],
        'show' => [],
        'hide' => [],
        'removeClassName' => [],
        'addClassName' => [],
        'refresh' => []
    ];

    /**
     * Validation messages.
     *
     * @var array
     */
    protected array $_messages = ['error' => [], 'success' => [], 'notice' => []];

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Add action for response.
     *
     * @param string $actionName
     * @param string $elementId
     * @param mixed $value OPTIONAL
     * @return $this
     */
    public function addAction($actionName, $elementId, $value = null): static
    {
        if (isset($this->_actions[$actionName])) {
            if (null === $value) {
                if (is_array($elementId)) {
                    foreach ($elementId as $oneId) {
                        $this->_actions[$actionName][] = $oneId;
                    }
                } else {
                    $this->_actions[$actionName][] = $elementId;
                }
            } else {
                $this->_actions[$actionName][$elementId] = $value;
            }
        }
        return $this;
    }

    /**
     * Add error message.
     *
     * @param \Magento\Framework\Phrase $message Error message
     * @return $this
     */
    public function addError(\Magento\Framework\Phrase $message): static
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addError($row);
            }
        } else {
            $this->_messages['error'][] = $this->escapeHtml($message);
        }
        return $this;
    }

    /**
     * Add notice message.
     *
     * @param string[]|string $message Message text
     * @param bool $appendImportButton OPTIONAL Append import button to message?
     * @return $this
     */
    public function addNotice($message, $appendImportButton = false): static
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addNotice($row);
            }
        } else {
            $this->_messages['notice'][] = $message . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Add success message.
     *
     * @param string[]|string $message Message text
     * @param bool $appendImportButton OPTIONAL Append import button to message?
     * @return $this
     */
    public function addSuccess($message, $appendImportButton = false): static
    {
        if (is_array($message)) {
            foreach ($message as $row) {
                $this->addSuccess($row);
            }
        } else {
            $escapedMessage = $this->escapeHtml($message);
            $this->_messages['success'][] = $escapedMessage . ($appendImportButton ? $this->getImportButtonHtml() : '');
        }
        return $this;
    }

    /**
     * Import button HTML for append to message.
     *
     * @return string
     */
    public function getImportButtonHtml(): string
    {
        return '&nbsp;&nbsp;<button onclick="varienImport.startImport(\'' .
            $this->getImportStartUrl() .
            '\', \'' .
            \Armada\ProductImport\Model\Import::FIELD_NAME_SOURCE_FILE .
            '\');" class="scalable save"' .
            ' type="button"><span><span><span>' .
            __(
                'Import'
            ) . '</span></span></span></button>';
    }

    /**
     * Import start action URL.
     *
     * @return string
     */
    public function getImportStartUrl(): string
    {
        return $this->getUrl('armada/*/start');
    }

    /**
     * Messages getter.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->_messages;
    }

    /**
     * Messages rendered HTML getter.
     *
     * @return string
     */
    public function getMessagesHtml(): string
    {
        /** @var $messagesBlock \Magento\Framework\View\Element\Messages */
        $messagesBlock = $this->_layout->createBlock(\Magento\Framework\View\Element\Messages::class);

        foreach ($this->_messages as $priority => $messages) {
            $method = "add{$priority}";

            foreach ($messages as $message) {
                $messagesBlock->{$method}($message);
            }
        }
        return $messagesBlock->toHtml();
    }

    /**
     * Return response as JSON.
     *
     * @return string
     */
    public function getResponseJson(): string
    {
        // add messages HTML if it is not already specified
        if (!isset($this->_actions['import_validation_messages'])) {
            $this->addAction('innerHTML', 'import_validation_messages', $this->getMessagesHtml());
        }
        return $this->_jsonEncoder->encode($this->_actions);
    }
}
