<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Pci
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Encryption key changer controller
 *
 */
class Enterprise_Pci_Adminhtml_Crypt_KeyController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check whether local.xml is writeable
     *
     * @return bool
     */
    protected function _checkIsLocalXmlWriteable()
    {
        $filename = Mage::getBaseDir('etc') . DS . 'local.xml';
        if (!is_writeable($filename)) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(
                Mage::helper('Enterprise_Pci_Helper_Data')->__('To make key change possible, make sure the following file is writeable: %s', realpath($filename))
            );
            return false;
        }
        return true;
    }

    /**
     * Render main page with form
     *
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Manage Encription Key'));

        $this->_checkIsLocalXmlWriteable();
        $this->loadLayout();
        $this->_setActiveMenu('Enterprise_Pci::system_crypt_key');

        if (($formBlock = $this->getLayout()->getBlock('pci.crypt.key.form'))
            && $data = Mage::getSingleton('Mage_Adminhtml_Model_Session')->getFormData(true)) {
            /* @var Enterprise_Pci_Block_Adminhtml_Crypt_Key_Form $formBlock */
            $formBlock->setFormData($data);
        }

        $this->renderLayout();
    }

    /**
     * Process saving new encryption key
     *
     */
    public function saveAction()
    {
        try {
            $key = null;
            if (!$this->_checkIsLocalXmlWriteable()) {
                throw new Exception('');
            }
            if (0 == $this->getRequest()->getPost('generate_random')) {
                $key = $this->getRequest()->getPost('crypt_key');
                if (empty($key)) {
                    throw new Exception(Mage::helper('Enterprise_Pci_Helper_Data')->__('Please enter an encryption key.'));
                }
                Mage::helper('Mage_Core_Helper_Data')->validateKey($key);
            }

            $newKey = Mage::getResourceSingleton('Enterprise_Pci_Model_Resource_Key_Change')
                ->changeEncryptionKey($key);
            Mage::getSingleton('Mage_Adminhtml_Model_Session')
                    ->addSuccess(
                Mage::helper('Enterprise_Pci_Helper_Data')->__('Encryption key has been changed.')
            );

            if (!$key) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addNotice(Mage::helper('Enterprise_Pci_Helper_Data')->__('Your new encryption key: <span style="font-family:monospace;">%s</span>. Please make a note of it and make sure you keep it in a safe place.', $newKey));
            }
            Mage::app()->cleanCache();
        }
        catch (Exception $e) {
            if ($message = $e->getMessage()) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError($e->getMessage());
            }
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setFormData(array('crypt_key' => $key));
        }
        $this->_redirect('*/*/');
    }

    /**
     * Check whether current administrator session allows this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('system/crypt_key');
    }
}
