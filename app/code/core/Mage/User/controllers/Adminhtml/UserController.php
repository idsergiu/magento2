<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_User
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_User_Adminhtml_UserController extends Mage_Backend_Controller_ActionAbstract
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('Mage_User::system_acl_users')
            ->_addBreadcrumb($this->__('System'), $this->__('System'))
            ->_addBreadcrumb($this->__('Permissions'), $this->__('Permissions'))
            ->_addBreadcrumb($this->__('Users'), $this->__('Users'))
        ;
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));
        /** @var $model Mage_User_Model_Resource_User */
        $model = Mage::getObjectManager()->get('Mage_User_Model_Resource_User');
        if (!$model->canCreateUser()) {
            /** @var $session Mage_Adminhtml_Model_Session */
            $session = Mage::getSingleton('Mage_Adminhtml_Model_Session');
            $session->addNotice($model->getMessageUserCreationProhibited());
        }
        $this->_initAction();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))
             ->_title($this->__('Permissions'))
             ->_title($this->__('Users'));

        $userId = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_User_Model_User');

        if ($userId) {
            $model->load($userId);
            if (! $model->getId()) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('This user no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_title($model->getId() ? $model->getName() : $this->__('New User'));

        // Restore previously entered form data from session
        $data = Mage::getSingleton('Mage_Backend_Model_Session')->getUserData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        Mage::register('permissions_user', $model);

        if (isset($userId)) {
            $breadcrumb = $this->__('Edit User');
        } else {
            $breadcrumb = $this->__('New User');
        }
        $this->_initAction()->_addBreadcrumb($breadcrumb, $breadcrumb);
        $this->renderLayout();
    }

    public function saveAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $data = $this->getRequest()->getPost();
        if (!$data) {
            $this->_redirect('*/*/');
            return;
        }
        /** @var $model Mage_User_Model_User */
        $model = $this->_objectManager->create('Mage_User_Model_User')->load($userId);
        if (!$model->getId() && $userId) {
            $this->_getSession()->addError($this->__('This user no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }
        try {
            $model->setData($data);
            $uRoles = $this->getRequest()->getParam('roles', array());
            if (count($uRoles)) {
                $model->setRoleId($uRoles[0]);
            }
            $model->save();
            $this->_getSession()->addSuccess($this->__('The user has been saved.'));
            $this->_getSession()->setUserData(false);
            $this->_redirect('*/*/');
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addMessages($e->getMessages());
            $this->_getSession()->setUserData($data);
            $this->_redirect('*/*/edit', array('_current' => true));
        }
    }

    public function deleteAction()
    {
        $currentUser = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getUser();

        if ($userId = $this->getRequest()->getParam('user_id')) {
            if ( $currentUser->getId() == $userId ) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError(
                    $this->__('You cannot delete your own account.')
                );
                $this->_redirect('*/*/edit', array('user_id' => $userId));
                return;
            }
            try {
                $model = Mage::getModel('Mage_User_Model_User');
                $model->setId($userId);
                $model->delete();
                Mage::getSingleton('Mage_Backend_Model_Session')->addSuccess($this->__('The user has been deleted.'));
                $this->_redirect('*/*/');
                return;
            }
            catch (Exception $e) {
                Mage::getSingleton('Mage_Backend_Model_Session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        Mage::getSingleton('Mage_Backend_Model_Session')->addError($this->__('Unable to find a user to delete.'));
        $this->_redirect('*/*/');
    }

    public function rolesGridAction()
    {
        $userId = $this->getRequest()->getParam('user_id');
        $model = Mage::getModel('Mage_User_Model_User');

        if ($userId) {
            $model->load($userId);
        }
        Mage::register('permissions_user', $model);
        $this->loadLayout();
        $this->renderLayout();
    }

    public function roleGridAction()
    {
        $this->loadLayout(false);
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_User::acl_users');
    }

}
