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
 * A parent class for backend controllers - contains directives for admin user creation and authentication
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.numberOfChildren)
 */
class Mage_Backend_Utility_Controller extends Magento_Test_TestCase_ControllerAbstract
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
        parent::setUp();

        Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_CONFIG);
        Mage::getSingleton('Mage_Backend_Model_Url')->turnOffSecretKey();

        $this->_auth = Mage::getSingleton('Mage_Backend_Model_Auth');
        $this->_session = $this->_auth->getAuthStorage();
        $this->_auth->login(Magento_Test_Bootstrap::ADMIN_NAME, Magento_Test_Bootstrap::ADMIN_PASSWORD);
    }

    protected function tearDown()
    {
        $this->_auth->logout();
        $this->_auth = null;
        $this->_session = null;

        Mage::getSingleton('Mage_Backend_Model_Url')->turnOnSecretKey();
        Mage::getConfig()->setCurrentAreaCode(null);

        parent::tearDown();
    }

    /**
     * Utilize backend session model by default
     *
     * @param PHPUnit_Framework_Constraint $constraint
     * @param string|null $messageType
     * @param string $sessionModel
     */
    public function assertSessionMessages(
        PHPUnit_Framework_Constraint $constraint, $messageType = null, $sessionModel = 'Mage_Backend_Model_Session'
    ) {
        parent::assertSessionMessages($constraint, $messageType, $sessionModel);
    }
}
