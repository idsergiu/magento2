<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * OAuth My Application grid block
 *
 * @category   Mage
 * @package    Mage_Oauth
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Oauth_Block_Adminhtml_Oauth_Admin_Token_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Construct grid block
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('adminTokenGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('entity_id')
            ->setDefaultDir(Varien_Db_Select::SQL_DESC);
    }

    /**
     * Prepare collection
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_Admin_Token_Grid
     */
    protected function _prepareCollection()
    {
        /** @var $user Mage_User_Model_User */
        $user = Mage::getSingleton('Mage_Backend_Model_Auth_Session')->getData('user');

        /** @var $collection Mage_Oauth_Model_Resource_Token_Collection */
        $collection = Mage::getModel('Mage_Oauth_Model_Token')->getCollection();
        $collection->joinConsumerAsApplication()
                ->addFilterByType(Mage_Oauth_Model_Token::TYPE_ACCESS)
                ->addFilterByAdminId($user->getId());
        $this->setCollection($collection);

        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare columns
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_Admin_Token_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('entity_id', array(
            'header'    => Mage::helper('Mage_Oauth_Helper_Data')->__('ID'),
            'index'     => 'entity_id',
            'align'     => 'right',
            'width'     => '50px',
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Application Name'),
            'index'     => 'name',
            'escape'    => true,
        ));

        /** @var $sourceYesNo Mage_Adminhtml_Model_System_Config_Source_Yesno */
        $sourceYesNo = Mage::getSingleton('Mage_Adminhtml_Model_System_Config_Source_Yesno');
        $this->addColumn('revoked', array(
            'header'    => $this->__('Revoked'),
            'index'     => 'revoked',
            'width'     => '100px',
            'type'      => 'options',
            'options'   => $sourceYesNo->toArray(),
            'sortable'  => true,
        ));

        parent::_prepareColumns();
        return $this;
    }

    /**
     * Add mass-actions to grid
     *
     * @return Mage_Oauth_Block_Adminhtml_Oauth_Admin_Token_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $block = $this->getMassactionBlock();

        $block->setFormFieldName('items');
        $block->addItem('enable', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Enable'),
            'url'   => $this->getUrl('*/*/revoke', array('status' => 0)),
        ));
        $block->addItem('revoke', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Revoke'),
            'url'   => $this->getUrl('*/*/revoke', array('status' => 1)),
        ));
        $block->addItem('delete', array(
            'label' => Mage::helper('Mage_Index_Helper_Data')->__('Delete'),
            'url'   => $this->getUrl('*/*/delete'),
        ));

        return $this;
    }

    /**
     * Get grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}
