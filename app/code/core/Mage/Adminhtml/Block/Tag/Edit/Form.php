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
 * Adminhtml tag edit form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tag_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_form');
        $this->setTitle(Mage::helper('Mage_Tag_Helper_Data')->__('Block Information'));
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('tag_tag');

        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post')
        );

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('Mage_Tag_Helper_Data')->__('General Information')));

        if ($model->getTagId()) {
            $fieldset->addField('tag_id', 'hidden', array(
                'name' => 'tag_id',
            ));
        }

        $fieldset->addField('form_key', 'hidden', array(
            'name'  => 'form_key',
            'value' => Mage::getSingleton('Mage_Core_Model_Session')->getFormKey(),
        ));

        $fieldset->addField('store_id', 'hidden', array(
            'name'  => 'store_id',
            'value' => (int)$this->getRequest()->getParam('store')
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'tag_name',
            'label' => Mage::helper('Mage_Tag_Helper_Data')->__('Tag Name'),
            'title' => Mage::helper('Mage_Tag_Helper_Data')->__('Tag Name'),
            'required' => true,
            'after_element_html' => ' ' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('[GLOBAL]'),
        ));

        $fieldset->addField('status', 'select', array(
            'label' => Mage::helper('Mage_Tag_Helper_Data')->__('BugsCoverage'),
            'title' => Mage::helper('Mage_Tag_Helper_Data')->__('BugsCoverage'),
            'name' => 'tag_status',
            'required' => true,
            'options' => array(
                Mage_Tag_Model_Tag::STATUS_DISABLED => Mage::helper('Mage_Tag_Helper_Data')->__('Disabled'),
                Mage_Tag_Model_Tag::STATUS_PENDING  => Mage::helper('Mage_Tag_Helper_Data')->__('Pending'),
                Mage_Tag_Model_Tag::STATUS_APPROVED => Mage::helper('Mage_Tag_Helper_Data')->__('Approved'),
            ),
            'after_element_html' => ' ' . Mage::helper('Mage_Adminhtml_Helper_Data')->__('[GLOBAL]'),
        ));

        $fieldset->addField('base_popularity', 'text', array(
            'name' => 'base_popularity',
            'label' => Mage::helper('Mage_Tag_Helper_Data')->__('Base Popularity'),
            'title' => Mage::helper('Mage_Tag_Helper_Data')->__('Base Popularity'),
            'after_element_html' => ' ' . Mage::helper('Mage_Tag_Helper_Data')->__('[STORE VIEW]'),
        ));

        if (!$model->getId() && !Mage::getSingleton('Mage_Adminhtml_Model_Session')->getTagData() ) {
            $model->setStatus(Mage_Tag_Model_Tag::STATUS_APPROVED);
        }

        if ( Mage::getSingleton('Mage_Adminhtml_Model_Session')->getTagData() ) {
            $form->addValues(Mage::getSingleton('Mage_Adminhtml_Model_Session')->getTagData());
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->setTagData(null);
        } else {
            $form->addValues($model->getData());
        }

        $this->setForm($form);
        return parent::_prepareForm();
    }
}
