<?php
/**
 * User role in role grid items updater
 *
 * @copyright {}
 */
class Mage_Webapi_Model_Acl_User_RoleUpdater implements Mage_Core_Model_Layout_Argument_UpdaterInterface
{
    /**
     * @var int
     */
    protected $_userId;

    /**
     * @var Mage_Webapi_Model_Acl_User_Factory
     */
    protected $_userFactory;

    /**
     * Constructor
     *
     * @param Mage_Core_Controller_Request_Http $request
     * @param Mage_Webapi_Model_Acl_User_Factory $userFactory
     */
    public function __construct(
        Mage_Core_Controller_Request_Http $request,
        Mage_Webapi_Model_Acl_User_Factory $userFactory
    ) {
        $this->_userId = (int)$request->getParam('user_id');
        $this->_userFactory = $userFactory;
    }

    /**
     * Init value with role assigned to user
     *
     * @param int|null $values
     * @return int|null
     */
    public function update($value)
    {
        if ($this->_userId) {
            $value = $this->_userFactory->create()->load($this->_userId)->getRoleId();
        }
        return $value;
    }
}
