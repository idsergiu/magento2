<?php
/**
 * Modules configuration proxy
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Config_Modules_Proxy implements Mage_Core_Model_Config_ModulesInterface
{
    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @var Mage_Core_Model_Config_Modules
     */
    protected $_model;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @return Mage_Core_Model_Config_Modules
     */
    protected function _getInstance()
    {
        if (null == $this->_model) {
            $this->_model = $this->_objectManager->get('Mage_Core_Model_Config_Modules');
        }

        return $this->_model;
    }

    /**
     * Get configuration node
     *
     * @param string $path
     * @return Varien_Simplexml_Element
     */
    public function getNode($path = null)
    {
        return $this->_getInstance()->getNode($path);
    }

    /**
     * Create node by $path and set its value
     *
     * @param string $path separated by slashes
     * @param string $value
     * @param boolean $overwrite
     */
    public function setNode($path, $value, $overwrite = true)
    {
        $this->_getInstance()->setNode($path, $value, $overwrite);
    }

    /**
     * Returns nodes found by xpath expression
     *
     * @param string $xpath
     * @return array
     */
    public function getXpath($xpath)
    {
        return $this->_getInstance()->getXpath($xpath);
    }

    /**
     * Get module config node
     *
     * @param string $moduleName
     * @return Varien_Simplexml_Element
     */
    public function getModuleConfig($moduleName = '')
    {
        return $this->_getInstance()->getModuleConfig($moduleName);
    }

    /**
     * Reinitialize primary configuration
     */
    public function reinit()
    {
        $this->_getInstance()->reinit();
    }
}
