<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Implementation of the @magentoDataFixture doc comment directive
 */
class Magento_Test_Listener_Annotation_Fixture
{
    /**
     * @var Magento_Test_Listener
     */
    protected $_listener;

    /**
     * Fixtures that have been applied
     *
     * @var array
     */
    private $_appliedFixtures = array();

    /**
     * Constructor
     *
     * @param Magento_Test_Listener $listener
     */
    public function __construct(Magento_Test_Listener $listener)
    {
        $this->_listener = $listener;
    }

    /**
     * Handler for 'endTestSuite' event
     */
    public function endTestSuite()
    {
        $this->_revertFixtures();
    }

    /**
     * Handler for 'startTest' event
     */
    public function startTest()
    {
        /* Apply fixtures declared on test case (class) and test (method) levels */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            /* Re-apply even the same fixtures to guarantee data consistency */
            $this->_revertFixtures();
            $this->_applyFixtures($methodFixtures);
        } else {
            $this->_applyFixtures($this->_getFixtures('class'));
        }
    }

    /**
     * Handler for 'endTest' event
     */
    public function endTest()
    {
        /* Isolate other tests from test-specific fixtures */
        $methodFixtures = $this->_getFixtures('method');
        if ($methodFixtures) {
            $this->_revertFixtures();
        }
    }

    /**
     * Retrieve fixtures from annotation
     *
     * @param string $scope 'class' or 'method'
     * @return array
     */
    protected function _getFixtures($scope)
    {
        $annotations = $this->_listener->getCurrentTest()->getAnnotations();
        if (!empty($annotations[$scope]['magentoDataFixture'])) {
            return $annotations[$scope]['magentoDataFixture'];
        }
        return array();
    }

    /**
     * Check whether the same connection is being used for both read and write operations
     *
     * @return bool
     */
    protected function _isSingleConnection()
    {
        $readAdapter  = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('read');
        $writeAdapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        return ($readAdapter === $writeAdapter);
    }

    /**
     * Start transaction
     *
     * @throws Exception
     */
    protected function _startTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $adapter->beginTransaction();
    }

    /**
     * Rollback transaction
     */
    protected function _rollbackTransaction()
    {
        /** @var $adapter Varien_Db_Adapter_Interface */
        $adapter = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        $adapter->rollBack();
    }

    /**
     * Execute single fixture script
     *
     * @param string|array $fixture
     */
    protected function _applyOneFixture($fixture)
    {
        if (is_callable($fixture)) {
            call_user_func($fixture);
        } else {
            require($fixture);
        }
    }

    /**
     * Execute fixture scripts if any
     *
     * @param array $fixtures
     */
    protected function _applyFixtures(array $fixtures)
    {
        if (empty($fixtures)) {
            return;
        }
        /* Start transaction before applying first fixture to be able to revert them all further */
        if (empty($this->_appliedFixtures)) {
            if (!$this->_isSingleConnection()) {
                throw new Exception('Transaction fixtures with 2 connections are not implemented yet.');
            }
            $this->_startTransaction();
        }
        /* Execute fixture scripts */
        foreach ($fixtures as $fixture) {
            if (strpos($fixture, '\\') !== false) {
                throw new Exception('The "\" symbol is not allowed for fixture definition.');
            }
            $fixtureMethod = array(get_class($this->_listener->getCurrentTest()), $fixture);
            $fixtureScript = realpath(__DIR__ . '/../../../../../testsuite') . DIRECTORY_SEPARATOR . $fixture;
            /* Skip already applied fixtures */
            if (in_array($fixtureMethod, $this->_appliedFixtures, true)
                || in_array($fixtureScript, $this->_appliedFixtures, true)
            ) {
                continue;
            }
            if (is_callable($fixtureMethod)) {
                $this->_applyOneFixture($fixtureMethod);
                $this->_appliedFixtures[] = $fixtureMethod;
            } else {
                $this->_applyOneFixture($fixtureScript);
                $this->_appliedFixtures[] = $fixtureScript;
            }
        }
    }

    /**
     * Revert changes done by fixtures
     */
    protected function _revertFixtures()
    {
        if (empty($this->_appliedFixtures)) {
            return;
        }
        $this->_rollbackTransaction();
        foreach ($this->_appliedFixtures as $fixture) {
            if (is_callable($fixture)) {
                $fixture[1] .= 'Rollback';
                if (is_callable($fixture)) {
                    $this->_applyOneFixture($fixture);
                }
            } else {
                $fileInfo = new SplFileInfo($fixture);

                $extension = $fileInfo->getExtension();
                $name = $fileInfo->getBasename(".{$extension}");

                $rollbackFile = $fileInfo->getPath() . DIRECTORY_SEPARATOR . $name . '_rollback.' . $extension;
                if (file_exists($rollbackFile)) {
                    $this->_applyOneFixture($rollbackFile);
                }
            }
        }
        $this->_appliedFixtures = array();
    }
}
