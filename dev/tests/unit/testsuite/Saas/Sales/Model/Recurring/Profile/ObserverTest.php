<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license {license_link}
 */
class Saas_Sales_Model_Recurring_Profile_ObserverTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var Saas_Sales_Model_Recurring_Profile_Observer
     */
    protected $_modelCacheObserver;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $this->_observerMock = $this->getMock('Varien_Event_Observer', array(), array(), '', false);
        $this->_helperMock = $this->getMock('Saas_Saas_Helper_Data', array(), array(), '', false);

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_modelCacheObserver = $objectManagerHelper->getObject('Saas_Sales_Model_Recurring_Profile_Observer',
            array('request' => $this->_requestMock, 'saasHelper' => $this->_helperMock)
        );
    }

    public function testDisabledSalesRecurringProfileBackendControllerBackend()
    {
        $this->_requestMock->expects($this->once())->method('getControllerName')
            ->will($this->returnValue('sales_recurring_profile'));
        $this->_requestMock->expects($this->once())->method('getControllerModule')
            ->will($this->returnValue('Mage_Adminhtml'));
        $this->_helperMock->expects($this->once())->method('customizeNoRoutForward')->with($this->_requestMock);

        $this->_modelCacheObserver->disableSalesRecurringProfileBackend($this->_observerMock);
    }

    /**
     * @param string $controller
     * @param string $module
     * @dataProvider dataProviderForNotDisabledSalesRecurringProfileBackendControllerBackend
     */
    public function testNotDisabledSalesRecurringProfileBackendControllerBackend($controller, $module)
    {
        $this->_requestMock->expects($this->any())->method('getControllerName')->will($this->returnValue($controller));
        $this->_requestMock->expects($this->any())->method('getControllerModule')->will($this->returnValue($module));
        $this->_helperMock->expects($this->never())->method('customizeNoRoutForward');

        $this->_modelCacheObserver->disableSalesRecurringProfileBackend($this->_observerMock);
    }

    /**
     * @return array
     */
    public function dataProviderForNotDisabledSalesRecurringProfileBackendControllerBackend()
    {
        return array(
            array('unknown', 'Mage_Adminhtml'),
            array('sales_recurring_profile', 'unknown'),
            array('unknown', 'unknown'),
        );
    }

    public function testDisabledSalesRecurringProfileControllerFrontend()
    {
        $this->_requestMock->expects($this->once())->method('getControllerName')
            ->will($this->returnValue('recurring_profile'));
        $this->_requestMock->expects($this->once())->method('getControllerModule')
            ->will($this->returnValue('Mage_Sales'));
        $this->_helperMock->expects($this->once())->method('customizeNoRoutForward')->with($this->_requestMock);

        $this->_modelCacheObserver->disableSalesRecurringProfileFrontend($this->_observerMock);
    }

    /**
     * @param string $controller
     * @param string $module
     * @dataProvider dataProviderForNotDisabledSalesRecurringProfileControllerFrontend
     */
    public function testNotDisabledSalesRecurringProfileControllerFrontend($controller, $module)
    {
        $this->_requestMock->expects($this->any())->method('getControllerName')->will($this->returnValue($controller));
        $this->_requestMock->expects($this->any())->method('getControllerModule')->will($this->returnValue($module));
        $this->_helperMock->expects($this->never())->method('customizeNoRoutForward');

        $this->_modelCacheObserver->disableSalesRecurringProfileFrontend($this->_observerMock);
    }

    /**
     * @return array
     */
    public function dataProviderForNotDisabledSalesRecurringProfileControllerFrontend()
    {
        return array(
            array('unknown', 'Mage_Sales'),
            array('recurring_profile', 'unknown'),
            array('unknown', 'unknown'),
        );
    }
}
