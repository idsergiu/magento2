<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Webservice api abstract
 *
 * @category   Mage
 * @package    Mage_Api
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api_Model_Server
{

    /**
     * Api Name by Adapter
     * @var string
     */
    protected $_api = "";

    /**
     * Web service adapter
     *
     * @var Mage_Api_Model_Server_Adapter_Soap
     */
    protected $_adapter;

    /**
     * Complex retrieve adapter code by calling auxiliary model method
     *
     * @param string $alias Alias name
     * @return string|null Returns NULL if no alias found
     */
    public function getAdapterCodeByAlias($alias)
    {
        /** @var $config Mage_Api_Model_Config */
        $config  = Mage::getSingleton('Mage_Api_Model_Config');
        $aliases = $config->getAdapterAliases();

        if (!isset($aliases[$alias])) {
            return null;
        }
        $object = Mage::getModel($aliases[$alias][0]);
        $method = $aliases[$alias][1];

        if (!method_exists($object, $method)) {
            Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Can not find webservice adapter.'));
        }
        return $object->$method();
    }

    /**
     * Initialize server components
     *
     * @param Mage_Api_Controller_Action $controller
     * @param string $adapter Adapter name
     * @param string $handler Handler name
     * @return Mage_Api_Model_Server
     */
    public function init(Mage_Api_Controller_Action $controller, $adapter = 'default', $handler = 'default')
    {
        $this->initialize($adapter, $handler);

        $this->_adapter->setController($controller);

        return $this;
    }

    /**
     * Initialize server components. Lightweight implementation of init() method
     *
     * @param string $adapterCode Adapter code
     * @param string $handler OPTIONAL Handler name (if not specified, it will be found from config)
     * @return Mage_Api_Model_Server
     */
    public function initialize($adapterCode, $handler = null)
    {
        /** @var $helper Mage_Api_Model_Config */
        $helper   = Mage::getSingleton('Mage_Api_Model_Config');
        $adapters = $helper->getActiveAdapters();

        if (isset($adapters[$adapterCode])) {
            /** @var $adapterModel Mage_Api_Model_Server_Adapter_Soap */
            $adapterModel = Mage::getModel((string) $adapters[$adapterCode]->model);

            if (!($adapterModel instanceof Mage_Api_Model_Server_Adapter_Soap)) {
                Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice adapter specified.'));
            }
            $this->_adapter = $adapterModel;
            $this->_api     = $adapterCode;

            // get handler code from config if no handler passed as argument
            if (null === $handler && !empty($adapters[$adapterCode]->handler)) {
                $handler = (string) $adapters[$adapterCode]->handler;
            }
            $handlers = $helper->getHandlers();

            if (!isset($handlers->$handler)) {
                Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice handler specified.'));
            }
            $handlerClassName = Mage::getConfig()->getModelClassName((string) $handlers->$handler->model);

            $this->_adapter->setHandler($handlerClassName);
        } else {
            Mage::throwException(Mage::helper('Mage_Api_Helper_Data')->__('Invalid webservice adapter specified.'));
        }
        return $this;
    }

    /**
     * Run server
     *
     */
    public function run()
    {
        $this->getAdapter()->run();
    }

    /**
     * Get Api name by Adapter
     * @return string
     */
    public function getApiName()
    {
        return $this->_api;
    }

    /**
     * Retrieve web service adapter
     *
     * @return Mage_Api_Model_Server_Adapter_Soap
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }


} // Class Mage_Api_Model_Server_Abstract End
