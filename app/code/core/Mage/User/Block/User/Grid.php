<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_User
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * User permissions user grid
 *
 * @category   Mage
 * @package    Mage_User
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_User_Block_User_Grid extends Mage_Backend_Block_Widget_Grid_Extended
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('permissionsUserGrid');
        $this->setDefaultSort('username');
        $this->setDefaultDir('asc');
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('Mage_User_Model_Resource_User_Collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('user_id', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('ID'),
            'width'     => 5,
            'align'     => 'right',
            'sortable'  => true,
            'index'     => 'user_id'
        ));

        $this->addColumn('username', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('User Name'),
            'index'     => 'username'
        ));

        $this->addColumn('firstname', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('First Name'),
            'index'     => 'firstname'
        ));

        $this->addColumn('lastname', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('Last Name'),
            'index'     => 'lastname'
        ));

        $this->addColumn('email', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('Email'),
            'width'     => 40,
            'align'     => 'left',
            'index'     => 'email'
        ));

        $this->addColumn('is_active', array(
            'header'    => Mage::helper('Mage_User_Helper_Data')->__('Status'),
            'index'     => 'is_active',
            'type'      => 'options',
            'options'   => array(
                '1' => Mage::helper('Mage_User_Helper_Data')->__('Active'),
                '0' => Mage::helper('Mage_User_Helper_Data')->__('Inactive')
            ),
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('user_id' => $row->getId()));
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/roleGrid', array());
    }
}
