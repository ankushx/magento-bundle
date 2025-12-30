<?php

namespace MiniOrange\IpRestriction\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ProductMetadataInterface;
use MiniOrange\IpRestriction\Helper\IpRestrictionConstants;

class Data extends AbstractHelper
{
    protected $scopeConfig;
    protected $configWriter;
    protected $cacheManager;
    protected $resource;
    protected $connection;
    protected $productMetadata;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\App\Cache\Manager $cacheManager,
        ResourceConnection $resource,
        ProductMetadataInterface $productMetadata
    ) {
        parent::__construct($context);
        $this->scopeConfig = $context->getScopeConfig();
        $this->configWriter = $configWriter;
        $this->cacheManager = $cacheManager;
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
        $this->productMetadata = $productMetadata;
    }

    public function getStoreConfig($config, $scope = 'default', $scopeId = 0)
    {
        if ($scope === 'stores') {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        } elseif ($scope === 'websites') {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE;
        } else {
            $storeScope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
        return $this->scopeConfig->getValue(IpRestrictionConstants::CONFIG_PATH_PREFIX . $config, $storeScope, $scopeId);
    }

    public function setStoreConfig($config, $value, $scope = 'default', $scopeId = 0)
    {
        $this->configWriter->save(IpRestrictionConstants::CONFIG_PATH_PREFIX . $config, $value, $scope, $scopeId);
        $this->cacheManager->flush(['config']);
    }

    public function getStoreConfigDirect($config, $scope = 'default', $scopeId = 0)
    {
        $tableName = $this->resource->getTableName('core_config_data');
        $path = IpRestrictionConstants::CONFIG_PATH_PREFIX . $config;
        
        $select = $this->connection->select()
            ->from($tableName, ['value'])
            ->where('path = ?', $path)
            ->where('scope = ?', $scope)
            ->where('scope_id = ?', $scopeId)
            ->limit(1);
        
        $value = $this->connection->fetchOne($select);
        
        return $value !== false ? $value : null;
    }

    /**
     * Flush cache comprehensively
     */
    public function flushCache()
    {
        $this->cacheManager->flush(['config', 'layout', 'block_html', 'collections', 'reflection', 'db_ddl', 'eav', 'config_integration', 'config_integration_api', 'full_page', 'translate', 'config_webservice_api']);
    }

    /**
     * Get current date
     * @return string
     */
    public function getCurrentDate(){
        $dateTimeZone = new \DateTimeZone('Asia/Kolkata'); 
        $dateTime = new \DateTime('now', $dateTimeZone);
        return $dateTime->format('n/j/Y, g:i:s a');
    }

    /**
     * Get product version
     * @return string
     */
    public function getProductVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Get edition
     * @return string
     */
    public function getEdition()
    {
        return $this->productMetadata->getEdition() == 'Community' ? 'Magento Open Source' : 'Adobe Commerce Enterprise/Cloud';
    }
}

