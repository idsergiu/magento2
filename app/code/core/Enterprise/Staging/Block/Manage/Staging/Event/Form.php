<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Staging event block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Manage_Staging_Event_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setFieldNameSuffix('event');
    }

    /**
     * Prepare form
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('event_fieldset',
            array('legend' => Mage::helper('enterprise_staging')->__('Staging Event Information')));

        $fieldset->addField('name', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Staging Name'),
            'title'     => Mage::helper('enterprise_staging')->__('Staging Name'),
            'name'      => 'name',
            'value'     => $this->getStagingName()
        ));

        $fieldset->addField('username', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Username'),
            'title'     => Mage::helper('enterprise_staging')->__('Username'),
            'name'      => 'username',
            'value'     => Mage::getModel('admin/user')->load($this->getEvent()->getUserId())->getUsername()
        ));

        $fieldset->addField('code', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Event Code'),
            'title'     => Mage::helper('enterprise_staging')->__('Event Code'),
            'name'      => 'code',
            'value'     => $this->getEvent()->getCode()
        ));

        $fieldset->addField('status', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Event Status'),
            'title'     => Mage::helper('enterprise_staging')->__('Event Status'),
            'name'      => 'status',
            'value'     => $this->getEvent()->getStatusLabel()
        ));

        $fieldset->addField('created_at', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Created At'),
            'title'     => Mage::helper('enterprise_staging')->__('Created At'),
            'name'      => 'created_at',
            'value'     => $this->formatDate($this->getEvent()->getCreatedAt(), 'medium' , true)
        ));

        $fieldset->addField('updated_at', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Updated At'),
            'title'     => Mage::helper('enterprise_staging')->__('Updated At'),
            'name'      => 'updated_at',
            'value'     => $this->formatDate($this->getEvent()->getUpdatedAt(), 'medium', true)
        ));


        $fieldset->addField('comment', 'label', array(
            'label'     => Mage::helper('enterprise_staging')->__('Comments'),
            'title'     => Mage::helper('enterprise_staging')->__('Comments'),
            'name'      => 'comment',
            'value'     => $this->getEvent()->getComment(),
            'readonly'  => true
        ));

        if ($this->getEvent()->getMergeScheduleDate() !== '0000-00-00 00:00:00') {
            $fieldset->addField('merge_schedule_date', 'label', array(
                'label'     => Mage::helper('enterprise_staging')->__('Schedule Date'),
                'title'     => Mage::helper('enterprise_staging')->__('Schedule Date'),
                'name'      => 'merge_schedule_date',
                'value'     => $this->formatDate($this->getEvent()->getMergeScheduleDate(), 'medium', true)
            ));
        }

        $form->setFieldNameSuffix($this->getFieldNameSuffix());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Retrieve staging object name
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStagingName()
    {
        $staging = Mage::registry('staging');
        if ($staging) {
            return $staging->getName();
        } else {
            return Mage::helper('enterprise_staging')->__('Staging not longer exists');
        }
    }

    /**
     * Retrieve event object
     *
     * @return Enterprise_Staging_Model_Staging_Event
     */
    public function getEvent()
    {
        if (!($this->getData('staging_event') instanceof Enterprise_Staging_Model_Staging_Event)) {
            $this->setData('staging_event', Mage::registry('staging_event'));
        }
        return $this->getData('staging_event');
    }

    /**
     * Retrieve formating date
     *
     * @param   string $date
     * @param   string $format
     * @param   bool $showTime
     * @return  string
     */
    public function formatDate($date=null, $format='short', $showTime=false)
    {
        if (is_string($date) && !(strpos($date, "0000")===0)) {
            $date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT);
        }

        if (strpos($date, "0000")===0) {
            return "";
        }

        return parent::formatDate($date, $format, $showTime);
    }
}
