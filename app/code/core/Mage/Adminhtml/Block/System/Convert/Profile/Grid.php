
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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profiles grid
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Moshe Gurvich <moshe@varien.com>
 */
class Mage_Adminhtml_Block_System_Convert_Profile_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('convertProfileGrid');
        $this->setDefaultSort('id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('core/convert_profile_collection')
            ->addFieldToFilter('entity_type', array('eq'=>''));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
            'header'    =>__('ID'),
            'width'     =>'50px',
            'index'     =>'profile_id',
        ));
        $this->addColumn('name', array(
            'header'    =>__('Profile Name'),
            'index'     =>'name',
        ));
        $this->addColumn('created_at', array(
            'header'    =>__('Created At'),
            'type'      => 'date',
            'align'     => 'center',
            'index'     =>'created_at',
        ));
        $this->addColumn('updated_at', array(
            'header'    =>__('Updated At'),
            'type'      => 'date',
            'align'     => 'center',
            'index'     =>'updated_at',
        ));
/*
        $this->addColumn('action', array(
            'header'	=> __('Action'),
            'index'		=> 'profile_id',
            'sortable'  => false,
            'filter' 	=> false,
            'width'		=> '100px',
            'renderer'  => 'adminhtml/system_convert_profile_grid_renderer_action'
        ));
*/
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return Mage::getUrl('*/*/edit', array('id'=>$row->getId()));
    }
}
