<?php
/**
 * @author Tony Xavier
 * @package Armada_ProductImport
 */
namespace Armada\ProductImport\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

/**
 * Import helper
 *
 */
class Import extends AbstractHelper
{
    /*
     * Product import configuration path.
     */
    const PRODUCT_IMPORT_CONFIG_PATH = 'armada_import/product/';

    const IMPORT_LOG_CONFIG_PATH = 'armada_import/log/';

    /**
     * Allowed product attributes list
     * Set "value" true for mark the attribute as "required"
     */
    protected $productAttributes = [
        'sku' => true,
        'price' => false,
        'status' => false,
        'qty'=> false,
        'is_in_stock'=> false
    ];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ){
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get Config value
     *
     * @return string|null
     */
    public function getConfigValue($field, $storeId = null, $path = self::PRODUCT_IMPORT_CONFIG_PATH)
    {
        $field = $path.$field;
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Check Product import enabled
     *
     * @return string|null
     */
    public function isEnabled(): ?string
    {
        return $this->getConfigValue("enabled");
    }

    /**
     * Get log cleanup days
     *
     * @return string|null
     */
    public function getLogCleanupDays(): ?string
    {
        return $this->getConfigValue("cleanup_days", self::IMPORT_LOG_CONFIG_PATH);
    }

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowedProductAttributes(): array
    {
        return $this->productAttributes;
    }
}
