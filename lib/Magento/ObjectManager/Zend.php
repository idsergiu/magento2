<?php

use Zend\Di\Di;

class Magento_ObjectManager_Zend extends Magento_ObjectManager_ObjectManagerAbstract
{
    /**
     * @var \Zend\Di\Di
     */
    protected $_di;

    /**
     * @var string
     */
    protected $_compileDir;

    /**
     * @var string
     */
    protected $_moduleDir;

    /**
     * @param Zend\Di\Di $di
     */
    public function __construct(\Zend\Di\Di $di)
    {
        $this->_di = $di;
        $di->instanceManager()->addSharedInstance($this, "Magento_ObjectManager");
        $config = $this->get('Mage_Core_Model_Config');
        $config->loadBase();
        $config = new Zend\Di\Configuration(array('instance' => $config->getNode('global/di')->asArray()));
        $config->configure($this->_di);

    }

    /**
     * Create new object instance
     *
     * @param string $className
     * @param array $arguments
     * @return mixed
     */
    public function create($className, array $arguments = array())
    {
        $ni =  $this->_di->newInstance($className, $arguments);
        return $ni;
    }

    /**
     * Retreive cached object instance
     *
     * @param string $objectName
     * @param array $arguments
     * @return mixed
     */
    public function get($className, array $arguments = array())
    {
        $ni = $this->_di->get($className, $arguments);
        return $ni;
    }

    /**
     * @param string $areaCode
     */
    public function loadAreaConfiguration($areaCode)
    {
        $node = $this->_di->get('Mage_Core_Model_Config')->getNode($areaCode . '/di');
        if ($node) {
            $config = new Zend\Di\Configuration(
                array('instance' => $node->asArray())
            );
            $config->configure($this->_di);
        }
    }
}
