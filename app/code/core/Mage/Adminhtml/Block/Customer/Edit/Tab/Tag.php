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
 * Customer's tags grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Customer_Edit_Tab_Tag extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('tag_grid');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
    }

    protected function _prepareCollection()
    {
        $tagId = Mage::registry('tagId');

        if( $this->getCustomerId() instanceof Mage_Customer_Model_Customer ) {
            $this->setCustomerId( $this->getCustomerId()->getId() );
        }

        $collection = Mage::getResourceModel('Mage_Tag_Model_Resource_Customer_Collection')
            ->addCustomerFilter($this->getCustomerId())
            ->addGroupByTag();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
            $this->getCollection()->addProductName();
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('name', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Tag Name'),
            'index'     => 'name',
        ));

        $this->addColumn('status', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('BugsCoverage'),
            'width'     => '90px',
            'index'     => 'status',
            'type'      => 'options',
            'options'    => array(
                Mage_Tag_Model_Tag::STATUS_DISABLED => Mage::helper('Mage_Customer_Helper_Data')->__('Disabled'),
                Mage_Tag_Model_Tag::STATUS_PENDING  => Mage::helper('Mage_Customer_Helper_Data')->__('Pending'),
                Mage_Tag_Model_Tag::STATUS_APPROVED => Mage::helper('Mage_Customer_Helper_Data')->__('Approved'),
            ),
            'filter'    => false,
        ));

        $this->addColumn('product', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('Product Name'),
            'index'     => 'product',
            'filter'    => false,
            'sortable'  => false,
        ));

        $this->addColumn('product_sku', array(
            'header'    => Mage::helper('Mage_Customer_Helper_Data')->__('SKU'),
            'index'     => 'product_sku',
            'filter'    => false,
            'sortable'  => false,
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/tag/edit', array(
            'tag_id' => $row->getTagId(),
            'customer_id' => $this->getCustomerId(),
        ));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/customer/tagGrid', array(
            '_current' => true,
            'id'       => $this->getCustomerId()
        ));
    }

}
