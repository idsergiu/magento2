<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Payment
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoAppArea adminhtml
 */
class Mage_Payment_Model_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Varien_Event_Observer
     */
    protected $_eventObserver;

    /**
     * @var Magento_Test_ObjectManager
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = Mage::getObjectManager();
        $this->_eventObserver = $this->_createEventObserver();
    }

    /**
     * Check that Mage_Payment_Model_Observer::updateOrderStatusForPaymentMethods()
     * is called as event and it can change status
     *
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Mage/Payment/_files/order_status.php
     */
    public function testUpdateOrderStatusForPaymentMethodsEvent()
    {
        $statusCode = 'custom_new_status';
        $data = array(
            'section' => 'payment',
            'website' => null,
            'store' => null,
            'groups' => array(
                'checkmo' => array(
                    'fields' => array(
                        'order_status' => array(
                            'value' => $statusCode
                        )
                    )
                )
            )
        );
        $this->_objectManager->create('Mage_Backend_Model_Config')
            ->setSection('payment')
            ->setGroups(array('groups' => $data['groups']))
            ->save();

        /** @var Mage_Sales_Model_Order_Status $status */
        $status = $this->_objectManager->get('Mage_Sales_Model_Order_Status')->load($statusCode);

        $defaultStatus = (string)Mage::getStoreConfig('payment/checkmo/order_status');

        /** @var Mage_Core_Model_Resource_Config $config */
        $config = $this->_objectManager->get('Mage_Core_Model_Resource_Config');
        $config->saveConfig('payment/checkmo/order_status', $statusCode , 'default', 0);

        $this->_resetConfig();

        $newStatus = Mage::getStoreConfig('payment/checkmo/order_status');

        $status->unassignState(Mage_Sales_Model_Order::STATE_NEW);

        $this->_resetConfig();

        $unassignedStatus = Mage::getStoreConfig('payment/checkmo/order_status');

        $this->assertEquals('pending', $defaultStatus);
        $this->assertEquals($statusCode, $newStatus);
        $this->assertEquals('pending', $unassignedStatus);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testUpdateOrderStatusForPaymentMethods()
    {
        $statusCode = 'custom_new_status';

        /** @var Mage_Core_Model_Resource_Config $config */
        $config = $this->_objectManager->get('Mage_Core_Model_Resource_Config');
        $config->saveConfig('payment/checkmo/order_status', $statusCode , 'default', 0);

        $this->_resetConfig();

        $observer = $this->_objectManager->create('Mage_Payment_Model_Observer');
        $observer->updateOrderStatusForPaymentMethods($this->_eventObserver);

        $this->_resetConfig();

        $unassignedStatus = Mage::getStoreConfig('payment/checkmo/order_status');
        $this->assertEquals('pending', $unassignedStatus);
    }

    /**
     * Create event observer
     *
     * @return Varien_Event_Observer
     */
    protected function _createEventObserver()
    {
        $data = array('status' => 'custom_new_status', 'state' => Mage_Sales_Model_Order::STATE_NEW);
        $event = $this->_objectManager->create('Varien_Event', array('data' => $data));
        return $this->_objectManager->create('Varien_Event_Observer', array('data' => array('event' => $event)));
    }

    /**
     * Clear config cache
     */
    protected function _resetConfig()
    {
        Mage::getConfig()->reinit();
        $this->_objectManager->create('Mage_Core_Model_StoreManagerInterface')->reinitStores();
    }
}
