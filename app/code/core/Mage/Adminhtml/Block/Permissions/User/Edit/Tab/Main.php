<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Cms page edit form main tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $model = Mage::registry('permissions_user');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('user_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Account Information')));

        if ($model->getUserId()) {
            $fieldset->addField('user_id', 'hidden', array(
                'name' => 'user_id',
            ));
        } else {
            if (! $model->hasData('is_active')) {
                $model->setIsActive(1);
            }
        }

        $fieldset->addField('username', 'text', array(
            'name'  => 'username',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Name'),
            'id'    => 'username',
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Name'),
            'required' => true,
        ));

        $fieldset->addField('firstname', 'text', array(
            'name'  => 'firstname',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('First Name'),
            'id'    => 'firstname',
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('First Name'),
            'required' => true,
        ));

        $fieldset->addField('lastname', 'text', array(
            'name'  => 'lastname',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Last Name'),
            'id'    => 'lastname',
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Last Name'),
            'required' => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name'  => 'email',
            'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Email'),
            'id'    => 'customer_email',
            'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('User Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
        ));

        if ($model->getUserId()) {
            $fieldset->addField('password', 'password', array(
                'name'  => 'new_password',
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('New Password'),
                'id'    => 'new_pass',
                'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('New Password'),
                'class' => 'input-text validate-admin-password',
            ));

            $fieldset->addField('confirmation', 'password', array(
                'name'  => 'password_confirmation',
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Password Confirmation'),
                'id'    => 'confirmation',
                'class' => 'input-text validate-cpassword',
            ));
        }
        else {
           $fieldset->addField('password', 'password', array(
                'name'  => 'password',
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Password'),
                'id'    => 'customer_pass',
                'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Password'),
                'class' => 'input-text required-entry validate-admin-password',
                'required' => true,
            ));
           $fieldset->addField('confirmation', 'password', array(
                'name'  => 'password_confirmation',
                'label' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Password Confirmation'),
                'id'    => 'confirmation',
                'title' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Password Confirmation'),
                'class' => 'input-text required-entry validate-cpassword',
                'required' => true,
            ));
        }

        if (Mage::getSingleton('Mage_Admin_Model_Session')->getUser()->getId() != $model->getUserId()) {
            $fieldset->addField('is_active', 'select', array(
                'name'  	=> 'is_active',
                'label' 	=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('This account is'),
                'id'    	=> 'is_active',
                'title' 	=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('Account Status'),
                'class' 	=> 'input-select',
                'style'		=> 'width: 80px',
                'options'	=> array('1' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Active'), '0' => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Inactive')),
            ));
        }

        $fieldset->addField('user_roles', 'hidden', array(
            'name' => 'user_roles',
            'id'   => '_user_roles',
        ));

        $data = $model->getData();

        unset($data['password']);

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
