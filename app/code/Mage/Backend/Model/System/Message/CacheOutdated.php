<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Model_System_Message_CacheOutdated implements Mage_Backend_Model_System_MessageInterface
{
    /**
     * @var Mage_Core_Model_UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var Mage_Core_Model_Authorization
     */
    protected $_authorization;

    /**
     * @var Mage_Core_Model_Cache
     */
    protected $_cache;

    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @param Mage_Core_Model_Authorization $authorization
     * @param Mage_Core_Model_UrlInterface $urlBuilder
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     */
    public function __construct(
        Mage_Core_Model_Authorization $authorization,
        Mage_Core_Model_UrlInterface $urlBuilder,
        Mage_Core_Model_Cache $cache,
        Mage_Core_Model_Factory_Helper $helperFactory
    ) {
        $this->_authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;
        $this->_cache = $cache;
        $this->_helperFactory = $helperFactory;
    }

    /**
     * Get array of cache types which require data refresh
     *
     * @return array
     */
    protected function _getCacheTypesForRefresh()
    {
        $output = array();
        foreach ($this->_cache->getInvalidatedTypes() as $type) {
            $output[] = $type->getCacheType();
        }
        return $output;
    }

    /**
     * Retrieve unique message identity
     *
     * @return string
     */
    public function getIdentity()
    {
        return md5('cache' . implode(':', $this->_getCacheTypesForRefresh()));
    }

    /**
     * Check whether
     *
     * @return bool
     */
    public function isDisplayed()
    {
        return $this->_authorization->isAllowed('Mage_Adminhtml::cache')
            && count($this->_getCacheTypesForRefresh()) > 0;
    }

    /**
     * Retrieve message text
     *
     * @return string
     */
    public function getText()
    {
        /** @var $helper Mage_Backend_Helper_Data */
        $helper = $this->_helperFactory->get('Mage_Backend_Helper_Data');
        $message = $helper->__('One or more of the Cache Types are invalidated: %s. ', implode(', ', $this->_getCacheTypesForRefresh()));
        $message .= $helper->__('Click here to go to Cache Management and refresh cache types.');
        return $message;
    }

    /**
     * Retrieve problem management url
     *
     * @return string|null
     */
    public function getLink()
    {
        return $this->_urlBuilder->getUrl('adminhtml/cache');
    }

    /**
     * Retrieve message severity
     *
     * @return int
     */
    public function getSeverity()
    {
        return Mage_Backend_Model_System_MessageInterface::SEVERITY_CRITICAL;
    }
}
