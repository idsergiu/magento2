<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Tag
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag
 */
class Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_TagTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test model
     *
     * @var Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag
     */
    protected $_model;

    /**
     * Expected constant data
     *
     * @var array
     */
    protected $_constantData = array(
        'id'        => 'tags',
        'is_hidden' => false,
        'after'     => 'reviews',
        'tab_class' => 'ajax',
    );

    /**
     * Array of data helpers
     *
     * @var array
     */
    protected $_helpers;

    public function setUp()
    {
        $dataHelper = $this->getMock('stdClass', array('__'));
        $dataHelper->expects($this->any())
            ->method('__')
            ->will($this->returnArgument(0));

        $this->_helpers = array('Mage_Tag_Helper_Data' => $dataHelper);
        $authSession = $this->getMock('Mage_Core_Model_Authorization', array(), array(), '', false);

        $arguments = array(
            'authSession' => $authSession,
            'urlBuilder' => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false),
            'data' => array(
                'helpers' => $this->_helpers,
            )
        );
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag', $arguments);
    }

    public function tearDown()
    {
        unset($this->_model);
    }

    /**
     * Prepare mock for testCanShowTab
     *
     * @param boolean $isCustomer
     * @param boolean $isCustomerExist
     * @param boolean $isAllowed
     */
    protected function _getMockForCanShowTab($isCustomer, $isCustomerExist, $isAllowed)
    {
        $customer = false;
        if ($isCustomer) {
            $customer = $this->getMock('Mage_Customer_Model_Customer', array('getId'), array(), '', false);
            $customer->expects($this->any())
                ->method('getId')
                ->will($this->returnValue($isCustomerExist));
        }

        $authSession = $this->getMock('Mage_Core_Model_Authorization', array('isAllowed'), array(), '', false);
        $authSession->expects($this->any())
            ->method('isAllowed')
            ->will($this->returnValue($isAllowed));

        $arguments = array(
            'authSession' => $authSession,
            'urlBuilder' => $this->getMock('Mage_Backend_Model_Url', array(), array(), '', false),
            'data' => array(
                'helpers' => $this->_helpers,
            )
        );
        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $objectManagerHelper->getObject('Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag', $arguments);
        if ($customer) {
            $this->_model->setCustomer($customer);
        }
    }

    /**
     * Test for constant data
     *
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::__construct
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::getTabLabel
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::getTabTitle
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::isHidden
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::getAfter
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::getTabClass
     */
    public function testConstantData()
    {
        $expectedTitle = $this->_model->getTitle();
        $this->assertNotEmpty($expectedTitle);
        $this->assertEquals($expectedTitle, $this->_model->getTabLabel());
        $this->assertEquals($expectedTitle, $this->_model->getTabTitle());

        $this->assertEquals($this->_constantData['id'], $this->_model->getId());
        $this->assertEquals($this->_constantData['is_hidden'], $this->_model->isHidden());
        $this->assertEquals($this->_constantData['after'], $this->_model->getAfter());
        $this->assertEquals($this->_constantData['tab_class'], $this->_model->getTabClass());
    }

    /**
     * Data provider for testCanShowTab
     *
     * @return array
     */
    public function canShowTabDataProvider()
    {
        return array(
            'no_customer' => array(
                '$isCustomer'      => false,
                '$isCustomerExist' => true,
                '$isAllowed'       => true,
                '$result'          => false,
            ),
            'new_customer_allowed' => array(
                '$isCustomer'      => true,
                '$isCustomerExist' => false,
                '$isAllowed'       => true,
                '$result'          => false,
            ),
            'new_customer_not_allowed' => array(
                '$isCustomer'      => true,
                '$isCustomerExist' => false,
                '$isAllowed'       => false,
                '$result'          => false,
            ),
            'existing_customer_allowed' => array(
                '$isCustomer'      => true,
                '$isCustomerExist' => true,
                '$isAllowed'       => true,
                '$result'          => true,
            ),
            'existing_customer_not_allowed' => array(
                '$isCustomer'      => true,
                '$isCustomerExist' => true,
                '$isAllowed'       => false,
                '$result'          => false,
            ),
        );
    }

    /**
     * Test for canShowTab method
     *
     * @param boolean $isCustomer
     * @param boolean $isCustomerExist
     * @param boolean $isAllowed
     * @param boolean $result
     *
     * @dataProvider canShowTabDataProvider
     * @covers Mage_Tag_Block_Adminhtml_Customer_Edit_Tab_Tag::canShowTab
     */
    public function testCanShowTab($isCustomer, $isCustomerExist, $isAllowed, $result)
    {
        $this->_getMockForCanShowTab($isCustomer, $isCustomerExist, $isAllowed);
        $this->assertSame($result, $this->_model->canShowTab());
    }
}

