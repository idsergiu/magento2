<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Staging
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Staging edit block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Adminhtml_Staging_Edit extends Mage_Adminhtml_Block_Widget
{

    protected $_template = 'staging/edit.phtml';

    protected function _construct()
    {
        parent::_construct();

        $this->setId('enterprise_staging_edit');

        $this->setEditFormJsObject('enterpriseStagingForm');
    }

    /**
     * Retrieve currently edited staging object
     *
     * @return Enterprise_Staging_Model_Staging
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }

    /**
     * Prepare layout
     */
    protected function _prepareLayout()
    {
        $this->addChild('back_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Back'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store', 0))).'\')',
            'class' => 'back'
        ));

        $this->addChild('reset_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Reset'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
        ));

        if ($this->getStaging()->canMerge()) {
            $this->addChild('merge_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Merge...'),
                'onclick'   => 'setLocation(\''.$this->getMergeUrl().'\')',
                'class'     => 'add'
            ));
        } elseif ($this->getStaging()->getId()) {
            $this->addChild('merge_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Merge'),
                'class'     => 'disabled'
            ));
        }

        if ($this->getStaging()->canSave()) {
            $this->addChild('save_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Save'),
                'class' => 'save',
                'data_attribute'  => array(
                    'mage-init' => array(
                        'button' => array('event' => 'save', 'target' => '#enterprise_staging_form'),
                    ),
                ),
            ));
        } else {
            if ($this->getRequest()->getParam('type')) {
                $this->addChild('create_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                    'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Create'),
                    'class'  => 'add',
                    'data_attribute'  => array(
                        'mage-init' => array(
                            'button' => array('event' => 'save', 'target' => '#enterprise_staging_form'),
                        ),
                    ),
                ));
            }
        }

        if ($this->getStaging()->canResetStatus()) {
            $this->addChild('reset_status_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Reset Status'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/resetStatus', array('_current'=>true)) . '\')',
                'class' => 'reset'
            ));
        }

        $stagingId = $this->getStagingId();
        if ($stagingId && $this->getStaging()->isScheduled()) {
            $this->addChild('unschedule_button', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label'     => Mage::helper('Enterprise_Staging_Helper_Data')->__('Unschedule Merge'),
                'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/unschedule', array('id' => $stagingId)) . '\')',
                'class' => 'reset'
            ));
        }

        return parent::_prepareLayout();
    }

    /**
     * Return Back button as html
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Return Cansel button as html
     */
    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Return Save button as html
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Return Save button as html
     */
    public function getResetStatusButtonHtml()
    {
        return $this->getChildHtml('reset_status_button');
    }

    /**
     * Return SaveandEdit button as html
     */
    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    /**
     * Return Merge button as html
     */
    public function getMergeButtonHtml()
    {
        return $this->getChildHtml('merge_button');
    }

    /**
     * Return validation url
     */
    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    /**
     * Return save url
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }

    /**
     * REturn SaveandEdit Url
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
            'tab'       => '{{tab_id}}'
        ));
    }

    /**
     * Return staging id
     */
    public function getStagingId()
    {
        return $this->getStaging()->getId();
    }

    /**
     * Return delete url
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current'=>true));
    }

    /**
     * Return merge url
     */
    public function getMergeUrl()
    {
        return $this->getUrl('*/*/merge', array('_current'=>true));
    }

    /**
     * Return sync url
     */
    public function getSyncUrl()
    {
        return $this->getUrl('*/*/sync', array('_current'=>true));
    }

    /**
     * Return rollback url
     */
    public function getRollbackUrl()
    {
        return $this->getUrl('*/*/rollback', array('_current'=>true));
    }

    /**
     * Return header
     */
    public function getHeader()
    {
        $header = '';
        if ($this->getStaging()->getId()) {
            $header = $this->escapeHtml($this->getStaging()->getName());
        } else {
            $header = Mage::helper('Enterprise_Staging_Helper_Data')->__('Create New Staging Website');
        }
        $setName = $this->getStagingEntitySetName();
        if ($setName) {
            $header.= ' (' . $setName . ')';
        }
        return $header;
    }

    /**
     * return selected table id
     *
     * @return string
     */
    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }

    /**
     * Retrieve master website id
     * if master website is not available return 0
     *
     * @return mixed
     */
    public function getMasterWebsiteId()
    {
        $website = $this->getStaging()->getMasterWebsite();
        if ($website) {
            return $website->getId();
        }

        return 0;
    }
}
