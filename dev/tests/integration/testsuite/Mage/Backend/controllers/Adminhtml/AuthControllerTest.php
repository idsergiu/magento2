<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test class for Mage_Backend_Adminhtml_AuthController
 */
class Mage_Backend_Adminhtml_AuthControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var Mage_Backend_Model_Auth_Session
     */
    protected $_session;

    /**
     * @var Mage_Backend_Model_Auth
     */
    protected $_auth;

    protected function setUp()
    {
        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_CONFIG);
        Mage::getConfig()->setCurrentAreaCode(Mage_Core_Model_App_Area::AREA_ADMINHTML);
        parent::setUp();
    }

    protected function tearDown()
    {
        $this->_session = null;
        $this->_auth = null;
        parent::tearDown();
        Mage::getConfig()->setCurrentAreaCode(null);
    }

    /**
     * Performs user login
     */
    protected  function _login()
    {
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();

        $this->_auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $this->_auth->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
        $this->_session = $this->_auth->getAuthStorage();
    }

    /**
     * Performs user logout
     */
    protected function _logout()
    {
        $this->_auth->logout();
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOnSecretKey();
    }

    /**
     * Check not logged state
     * @covers Mage_Backend_Adminhtml_AuthController::loginAction
     */
    public function testNotLoggedLoginAction()
    {
        $this->dispatch('backend/admin/auth/login');
        $this->assertFalse($this->getResponse()->isRedirect());
        $expected = 'Log in to Admin Panel';
        $this->assertContains($expected, $this->getResponse()->getBody(), 'There is no login form');
    }

    /**
     * Check logged state
     * @covers Mage_Backend_Adminhtml_AuthController::loginAction
     * @magentoDbIsolation enabled
     */
    public function testLoggedLoginAction()
    {
        $this->_login();

        $this->dispatch('backend/admin/auth/login');
        /** @var $backendUrlModel Mage_Backend_Model_Url */
        $backendUrlModel = Mage::getObjectManager()->get('Mage_Backend_Model_Url');
        $expected = $backendUrlModel->getUrl('adminhtml/dashboard');
        $this->assertRedirect($this->stringStartsWith($expected));

        $this->_logout();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testNotLoggedLoginActionWithRedirect()
    {
        $this->getRequest()->setPost(array(
            'login' => array(
                'username' => Magento_Test_Bootstrap::ADMIN_NAME,
                'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
            )
        ));

        $this->dispatch('backend/admin/index/index');

        $response = Mage::app()->getResponse();
        $code = $response->getHttpResponseCode();
        $this->assertTrue($code >= 300 && $code < 400, 'Incorrect response code');

        $this->assertTrue(Mage::getSingleton('Mage_Backend_Model_Auth')->isLoggedIn());
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::logoutAction
     * @magentoDbIsolation enabled
     */
    public function testLogoutAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/logout');
        $this->assertRedirect($this->equalTo(Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl()));
        $this->assertFalse($this->_session->isLoggedIn(), 'User is not logouted');
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::deniedJsonAction
     * @covers Mage_Backend_Adminhtml_AuthController::_getDeniedJson
     * @magentoDbIsolation enabled
     */
    public function testDeniedJsonAction()
    {
        $this->_login();
        $this->dispatch('backend/admin/auth/deniedJson');
        $data = array(
            'ajaxExpired' => 1,
            'ajaxRedirect' => Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl(),
        );
        $expected = json_encode($data);
        $this->assertEquals($expected, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * @covers Mage_Backend_Adminhtml_AuthController::deniedIframeAction
     * @covers Mage_Backend_Adminhtml_AuthController::_getDeniedIframe
     * @magentoDbIsolation enabled
     */
    public function testDeniedIframeAction()
    {
        $this->_login();
        $homeUrl = Mage::helper('Mage_Backend_Helper_Data')->getHomePageUrl();
        $this->dispatch('backend/admin/auth/deniedIframe');
        $expected = '<script type="text/javascript">parent.window.location =';
        $this->assertStringStartsWith($expected, $this->getResponse()->getBody());
        $this->assertContains($homeUrl, $this->getResponse()->getBody());
        $this->_logout();
    }

    /**
     * Test user logging process when user not assigned to any role
     * @dataProvider incorrectLoginDataProvider
     * @magentoDbIsolation enabled
     *
     * @param $params
     */
    public function testIncorrectLogin($params)
    {
        $this->getRequest()->setPost($params);
        $this->dispatch('backend/admin/auth/login');
        $this->assertContains('Invalid User Name or Password', $this->getResponse()->getBody());
    }

    public function incorrectLoginDataProvider()
    {
        return array(
            'login dummy user' => array (
                array(
                    'login' => array(
                        'username' => 'test1',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
            'login without role' => array (
                array(
                    'login' => array(
                        'username' => 'test2',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
            'login not active user' => array (
                array(
                    'login' => array(
                        'username' => 'test3',
                        'password' => Magento_Test_Bootstrap::ADMIN_PASSWORD,
                    )
                ),
            ),
        );
    }
}
