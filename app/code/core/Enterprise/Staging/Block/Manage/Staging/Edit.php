<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Staging edit block
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Block_Manage_Staging_Edit extends Mage_Adminhtml_Block_Widget
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('enterprise/staging/manage/staging/edit.phtml');
        $this->setId('enterprise_staging_edit');
    }

    /**
     * Retrieve currently edited staging object
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getStaging()
    {
        if (!($this->getData('staging') instanceof Enterprise_Staging_Model_Staging)) {
            $this->setData('staging', Mage::registry('staging'));
        }
        return $this->getData('staging');
    }

    protected function _prepareLayout()
    {
        if (!$this->getRequest()->getParam('popup')) {
            $this->setChild('back_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Back'),
                        'onclick'   => 'setLocation(\''.$this->getUrl('*/*/', array('store'=>$this->getRequest()->getParam('store', 0))).'\')',
                        'class' => 'back'
                    ))
            );
        } else {
            $this->setChild('back_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Close Window'),
                        'onclick'   => 'window.close()',
                        'class' => 'cancel'
                    ))
            );
        }

        $this->setChild('reset_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('enterprise_staging')->__('Reset'),
                    'onclick'   => 'setLocation(\''.$this->getUrl('*/*/*', array('_current'=>true)).'\')'
                ))
        );

        $this->setChild('save_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('enterprise_staging')->__('Save'),
                    'onclick'   => 'productForm.submit()',
                    'class' => 'save'
                ))
        );

        if (!$this->getRequest()->getParam('popup')) {
            $this->setChild('save_and_edit_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Save And Continue Edit'),
                        'onclick'   => 'saveAndContinueEdit(\''.$this->getSaveAndContinueUrl().'\')',
                        'class'     => 'save'
                    ))
            );
            $this->setChild('delete_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Delete'),
                        'onclick'   => 'confirmSetLocation(\''.Mage::helper('enterprise_staging')->__('Are you sure?').'\', \''.$this->getDeleteUrl().'\')',
                        'class'  => 'delete'
                    ))
            );
            $this->setChild('sync_button',
                $this->getLayout()->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label'     => Mage::helper('enterprise_staging')->__('Sync'),
                        'onclick'   => 'setLocation(\''.$this->getSyncUrl().'\')',
                        'class'  => 'sync'
                    ))
            );
            if ($this->getStaging()->getId()) {
                $this->setChild('merge_button',
                    $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label'     => Mage::helper('enterprise_staging')->__('Merge'),
                            'onclick'   => 'setLocation(\''.$this->getMergeUrl().'\')',
                            'class'  => 'add'
                        ))
                );
            } else {
                $this->setChild('create_button',
                    $this->getLayout()->createBlock('adminhtml/widget_button')
                        ->setData(array(
                            'label'     => Mage::helper('enterprise_staging')->__('Create'),
                            'onclick'   => 'stagingCreate(\''.$this->getCreateUrl().'\')',
                            'class'  => 'add'
                        ))
                );
            }
        }

        return parent::_prepareLayout();
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    public function getCancelButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveAndEditButtonHtml()
    {
        return $this->getChildHtml('save_and_edit_button');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getRollbackButtonHtml()
    {
        return $this->getChildHtml('rollback_button');
    }

    public function getMergeButtonHtml()
    {
        return $this->getChildHtml('merge_button');
    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true, 'back'=>null));
    }

    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', array(
            '_current'  => true,
            'back'      => 'edit',
            'tab'       => '{{tab_id}}'
        ));
    }

    public function getStagingId()
    {
        return $this->getStaging()->getId();
    }

    public function getDatasetId()
    {
        if (!($setId = $this->getStaging()->getDatasetId()) && $this->getRequest()) {
            $setId = $this->getRequest()->getParam('set', null);
        }
        return $setId;
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', array('_current'=>true));
    }

    public function getMergeUrl()
    {
        return $this->getUrl('*/*/merge', array('_current'=>true));
    }

    public function getSyncUrl()
    {
        return $this->getUrl('*/*/sync', array('_current'=>true));
    }

    public function getHeader()
    {
        $header = '';
        if ($this->getStaging()->getId()) {
            $header = $this->htmlEscape($this->getStaging()->getName());
        } else {
            $header = Mage::helper('enterprise_staging')->__('Create New Staging');
        }
        $setName = $this->getStagingEntitySetName();
        if ($setName) {
            $header.= ' (' . $setName . ')';
        }
        return $header;
    }

    public function getStagingEntitySetName()
    {
    	$setId = $this->getStaging()->getStagingEntitySetId();
        if ($setId) {
            $set = Mage::getModel('enterprise_staging/staging_entity_set')
                ->load($setId);
            return $set->getName();
        }
        return '';
    }

    public function getIsConfigurable()
    {
        return $this->getStaging()->isConfigurable();
    }

    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }
}
