<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Catalog
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test service layer Mage_Service_Customer
 */
class Mage_Service_CustomerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Customer_Service_Customer
     */
    protected $_model;

    /**
     * @var Mage_Customer_Model_Customer
     */
    protected $_createdCustomer;

    protected function setUp()
    {
        $this->_model = new Mage_Customer_Service_Customer();
    }

    protected function tearDown()
    {
        Mage::app()->setCurrentStore(Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID));
        if ($this->_createdCustomer && $this->_createdCustomer->getId() > 0) {
            $this->_createdCustomer->delete();
        }
        $this->_model = null;
    }

    /**
     * @param array $customerData
     * @param string $exceptionName
     * @dataProvider initCreateCustomerDataProvider
     */
    public function testCreate($customerData, $exceptionName = '')
    {
        if (!empty($exceptionName)) {
            $this->setExpectedException($exceptionName);
        }

        $this->_createdCustomer = $this->_model->create($customerData);
        $this->assertInstanceOf('Mage_Customer_Model_Customer', $this->_createdCustomer);
        $this->assertGreaterThan(0, $this->_createdCustomer->getId());
    }

    /**
     * @param array $customerData
     * @param string $exceptionName
     * @dataProvider initUpdateCustomerDataProvider
     */
    public function testUpdate($customerData, $exceptionName = '')
    {
        $customerInitData = $this->initCreateCustomerDataProvider();
        $this->_createdCustomer = $this->_model->create($customerInitData[0][0]);

        $this->assertInstanceOf('Mage_Customer_Model_Customer', $this->_createdCustomer);
        $this->assertGreaterThan(1, $this->_createdCustomer->getId());

        if (!empty($exceptionName)) {
            $this->setExpectedException($exceptionName);
        }

        $updatedCustomer = $this->_model->update($this->_createdCustomer->getId(), $customerData);

        $this->assertInstanceOf('Mage_Customer_Model_Customer', $updatedCustomer);
        $this->assertGreaterThan(1, $updatedCustomer->getId());

        foreach ($customerData['account'] as $key => $val) {
            $this->assertEquals($val, $updatedCustomer->getData($key));
        }
    }

    /**
     * @param array $customerData
     * @dataProvider initForbiddenFieldsUpdateDataProvider
     */
    public function testForbiddenFieldsUpdate($customerData)
    {
        $customerInitData = $this->initCreateCustomerDataProvider();
        $this->_createdCustomer = $this->_model->create($customerInitData[0][0]);

        $this->assertInstanceOf('Mage_Customer_Model_Customer', $this->_createdCustomer);
        $this->assertGreaterThan(1, $this->_createdCustomer->getId());

        $updatedCustomer = $this->_model->update($this->_createdCustomer->getId(), $customerData);

        $this->assertInstanceOf('Mage_Customer_Model_Customer', $updatedCustomer);
        $this->assertGreaterThan(1, $updatedCustomer->getId());

        foreach ($customerData['account'] as $key => $val) {
            $this->assertNotEquals($val, $updatedCustomer->getData($key));
        }
    }

    /**
     * @return array
     */
    public function initCreateCustomerDataProvider()
    {
        return array(
            array(array('account' => array('website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => 'SomeName',
                'middlename' => null,
                'lastname' => 'SomeSurname',
                'suffix' => null,
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'dob' => null,
                'taxvat' => null,
                'gender' => 1,
                'password' => '123123q',
                'default_billing' => null,
                'default_shipping' => null,
            ))),
            array(array('account' => array('website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => null,
                'lastname' => 'SomeSurname',
                'suffix' => null,
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'password' => '123123q',
            )), 'Mage_Core_Exception'),
            array(array('account' => array('website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => 'SomeName',
                'lastname' => 'SomeSurname',
                'suffix' => null,
                'email' => '111@111',
                'password' => '123123q',
            )), 'Mage_Core_Exception'),
            array(array('account' => array('website_id' => 0,
                'group_id' => 1,
                'disable_auto_group_change' => 0,
                'prefix' => null,
                'firstname' => 'SomeName',
                'lastname' => 'SomeSurname',
                'suffix' => null,
                'email' => 'test' . mt_rand(1000, 9999) . '@mail.com',
                'password' => '123',
            )), 'Mage_Eav_Model_Entity_Attribute_Exception'),
        );
    }

    /**
     * @return array
     */
    public function initUpdateCustomerDataProvider()
    {
        return array(
            array(array('account' => array(
                'password' => '111111',
            ))),
            array(array('account' => array(
                'password' => '111'
            )), 'Mage_Eav_Model_Entity_Attribute_Exception'),
            array(array('account' => array(
                'firstname' => null
            )), 'Mage_Core_Exception'),
            array(array('account' => array(
                'email' => '3434@23434'
            )), 'Mage_Core_Exception'),
        );
    }

    /**
     * @return array
     */
    public function initForbiddenFieldsUpdateDataProvider()
    {
        return array(
            array(array('account' => array(
                'website_id' => 111,
                'entity_type_id' => 555,
            ))),
        );
    }

    /**
     * @magentoDataFixture Mage/Customer/_files/customer.php
     */
    public function testGetList()
    {
        $expected = new Mage_Customer_Model_Customer();
        $expected->load(1);
        $actual = $this->_model->getList(array(
            'page' => 1,
            'limit' => 1,
            'order' => 'entity_id',
            'dir' => 'asc',
            'filter' => array(
                array(
                    'attribute' => 'entity_id',
                    'eq' => 1
                )
            )
        ), '*');

        $this->assertInternalType('array', $actual);
        $this->assertCount(1, $actual);
        $this->assertEquals($expected->toArray(), $actual[1]->toArray());
    }
}
