<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class for basic object retrieving, such as blocks, models etc...
 */
class Magento_Test_Helper_ObjectManager
{
    /**#@+
     * Supported entities keys.
     */
    const BLOCK_ENTITY = 'block';
    const MODEL_ENTITY = 'model';
    /**#@-*/

    /**
     * List of supported entities which can be initialized with their dependencies
     * Example:
     * array(
     *     'entityName' => array(
     *         'paramName' => 'Mage_Class_Name' or 'callbackMethod'
     *     )
     * );
     *
     * @var array
     */
    protected $_supportedEntities = array(
        self::BLOCK_ENTITY => array(
            'context' => '_getBlockTemplateContext',
        ),
        self::MODEL_ENTITY => array(
            'eventDispatcher'    => 'Mage_Core_Model_Event_Manager',
            'cacheManager'       => 'Mage_Core_Model_Cache',
            'resource'           => '_getResourceModelMock',
            'resourceCollection' => 'Varien_Data_Collection_Db',
            'filesystem'         => 'Magento_Filesystem',
        )
    );

    /**
     * Test object
     *
     * @var PHPUnit_Framework_TestCase
     */
    protected $_testObject;

    /**
     * Class constructor
     *
     * @param PHPUnit_Framework_TestCase $testObject
     */
    public function __construct(PHPUnit_Framework_TestCase $testObject)
    {
        $this->_testObject = $testObject;
    }

    /**
     * Get block instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Block_Abstract
     */
    public function getBlock($className, array $arguments = array())
    {
        $arguments = $this->getConstructArguments(self::BLOCK_ENTITY, $className, $arguments);
        return $this->_getInstanceViaConstructor($className, $arguments);
    }

    /**
     * Get model instance
     *
     * @param string $className
     * @param array $arguments
     * @return Mage_Core_Model_Abstract
     */
    public function getModel($className, array $arguments = array())
    {
        $arguments = $this->getConstructArguments(self::MODEL_ENTITY, $className, $arguments);
        return $this->_getInstanceViaConstructor($className, $arguments);
    }

    /**
     * Retrieve list of arguments that used for new block instance creation
     *
     * @param string $entityName
     * @param string $className
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return array
     */
    public function getConstructArguments($entityName, $className = '', array $arguments = array())
    {
        if (!array_key_exists($entityName, $this->_supportedEntities)) {
            throw new InvalidArgumentException('Unsupported entity type');
        }

        $constructArguments = array();
        foreach ($this->_supportedEntities[$entityName] as $propertyName => $propertyType) {
            if (!isset($arguments[$propertyName])) {
                if (method_exists($this, $propertyType)) {
                    $constructArguments[$propertyName] = $this->$propertyType($arguments);
                } else {
                    $constructArguments[$propertyName] = $this->_getMockWithoutConstructorCall($propertyType);
                }
            }
        }
        $constructArguments = array_merge($constructArguments, $arguments);

        if ($className) {
            return $this->_sortConstructorArguments($className, $constructArguments);
        } else {
            return $constructArguments;
        }
    }

    /**
     * Retrieve specific mock of core resource model
     *
     * @return Mage_Core_Model_Resource_Resource|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getResourceModelMock()
    {
        $resourceMock = $this->_testObject->getMock('Mage_Core_Model_Resource_Resource', array('getIdFieldName'),
            array(), '', false
        );
        $resourceMock->expects($this->_testObject->any())
            ->method('getIdFieldName')
            ->will($this->_testObject->returnValue('id'));

        return $resourceMock;
    }

    /**
     * Create context object
     * @param array $arguments
     * @return Mage_Core_Block_Template_Context
     */
    protected function _getBlockTemplateContext(array $arguments)
    {
        $params = array(
            'request' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Controller_Request_Http', array(), array(), '', false),
                ),
            ),
            'layout' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Layout', array(), array(), '', false),
                ),
            ),
            'eventManager' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Event_Manager', array(), array(), '', false),
                ),
            ),
            'urlBuilder' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Url', array(), array(), '', false),
                ),
            ),
            'translator' => array(
                'default' => array(
                    'object' => $this,
                    'method' => '_getTranslatorMock',
                    'params' => array(),
                ),
            ),
            'cache' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Cache', array(), array(), '', false),
                ),
            ),
            'designPackage' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Design_Package', array(), array(), '', false),
                ),
            ),
            'session' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Session', array(), array(), '', false),
                ),
            ),
            'storeConfig' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Store_Config', array(), array(), '', false),
                ),
            ),
            'frontController' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Controller_Varien_Front', array(), array(), '', false),
                ),
            ),
            'helperFactory' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Factory_Helper', array(), array(), '', false)
                ),
            ),
            'dirs' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Dir', array(), array(), '', false)
                ),
            ),
            'logger' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Mage_Core_Model_Logger', array(), array(), '', false)
                ),
            ),
            'filesystem' => array(
                'default' => array(
                    'object' => $this->_testObject,
                    'method' => 'getMock',
                    'params' => array('Magento_Filesystem', array(), array(), '', false)
                ),
            ),
        );

        $parameters = array();
        foreach ($params as $name => $default) {
            if (isset($arguments[$name])) {
                $parameters[$name] = $arguments[$name];
            } else {
                $config = $default['default'];
                $parameters[$name] = call_user_func_array(array($config['object'],
                    $config['method']), $config['params']
                );
            }
        }

        list($request, $layout, $eventManager, $urlBuilder, $translator, $cache, $designPackage, $session,
            $storeConfig, $frontController, $helperFactory, $dirs, $logger, $filesystem) = array_values($parameters);

        $context = new Mage_Core_Block_Template_Context($request, $layout, $eventManager, $urlBuilder, $translator,
            $cache, $designPackage, $session, $storeConfig, $frontController, $helperFactory, $dirs, $logger,
            $filesystem
        );
        return $context;
    }


    /**
     * Retrieve mock of core translator model
     *
     * @return Mage_Core_Model_Translate|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getTranslatorMock()
    {
        $translator = $this->_testObject->getMockBuilder('Mage_Core_Model_Translate')
            ->disableOriginalConstructor()
            ->setMethods(array('translate'))
            ->getMock();
        $translateCallback = function ($arguments) {
            $result = '';
            if (is_array($arguments) && current($arguments) instanceof Mage_Core_Model_Translate_Expr) {
                /** @var Mage_Core_Model_Translate_Expr $expression */
                $expression = array_shift($arguments);
                $result = vsprintf($expression->getText(), $arguments);
            }
            return $result;
        };
        $translator->expects($this->_testObject->any())
            ->method('translate')
            ->will($this->_testObject->returnCallback($translateCallback));
        return $translator;
    }

    /**
     * Sort constructor arguments array as is defined for current class interface
     *
     * @param string $className
     * @param array $arguments
     * @return array
     */
    protected function _sortConstructorArguments($className, array $arguments)
    {
        $constructArguments = array();
        $method = new ReflectionMethod($className, '__construct');
        foreach ($method->getParameters() as $parameter) {
            $parameterName = $parameter->getName();
            if (isset($arguments[$parameterName])) {
                $constructArguments[$parameterName] = $arguments[$parameterName];
            } else {
                if ($parameter->isDefaultValueAvailable()) {
                    $constructArguments[$parameterName] = $parameter->getDefaultValue();
                } else {
                    $constructArguments[$parameterName] = null;
                }
            }
        }

        return $constructArguments;
    }

    /**
     * Get mock without call of original constructor
     *
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getMockWithoutConstructorCall($className)
    {
        return $this->_testObject->getMock($className, array(), array(), '', false);
    }

    /**
     * Get class instance via constructor
     *
     * @param string $className
     * @param array $arguments
     * @return object
     */
    protected function _getInstanceViaConstructor($className, array $arguments = array())
    {
        $reflectionClass = new ReflectionClass($className);
        return $reflectionClass->newInstanceArgs($arguments);
    }
}
