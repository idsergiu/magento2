<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_Rma
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * RMA Grid
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Rma_Block_Adminhtml_Rma_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Initialize grid
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('rmaGrid');
        $this->setDefaultSort('date_requested');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare related item collection
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareCollection()
    {
        $this->_beforePrepareCollection();
        return parent::_prepareCollection();
    }

    /**
     * Configuring and setting collection
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _beforePrepareCollection()
    {
        if (!$this->getCollection()) {
            $collection = Mage::getResourceModel('Enterprise_Rma_Model_Resource_Rma_Grid_Collection');
            $this->setCollection($collection);
        }
        return $this;
    }

    /**
     * Prepare grid columns
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('increment_id', array(
            'header' => Mage::helper('Enterprise_Rma_Helper_Data')->__('RMA #'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'increment_id'
        ));

        $this->addColumn('date_requested', array(
            'header' => Mage::helper('Enterprise_Rma_Helper_Data')->__('Date Requested'),
            'index' => 'date_requested',
            'type' => 'datetime',
            'html_decorators' => array('nobr'),
            'width' => 1,
        ));

        $this->addColumn('order_increment_id', array(
            'header' => Mage::helper('Enterprise_Rma_Helper_Data')->__('Order #'),
            'width'  => '50px',
            'type'   => 'number',
            'index'  => 'order_increment_id'
        ));

        $this->addColumn('order_date', array(
            'header' => Mage::helper('Enterprise_Rma_Helper_Data')->__('Order Date'),
            'index' => 'order_date',
            'type' => 'datetime',
            'html_decorators' => array('nobr'),
            'width' => 1,
        ));

        $this->addColumn('customer_name', array(
            'header' => Mage::helper('Enterprise_Rma_Helper_Data')->__('Customer Name'),
            'index' => 'customer_name',
        ));

        $this->addColumn('status', array(
            'header'  => Mage::helper('Enterprise_Rma_Helper_Data')->__('BugsCoverage'),
            'index'   => 'status',
            'type'    => 'options',
            'width'   => '100px',
            'options' => Mage::getModel('Enterprise_Rma_Model_Rma')->getAllStatuses()
        ));

        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('Enterprise_Rma_Helper_Data')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('Enterprise_Rma_Helper_Data')->__('View'),
                        'url'       => array('base'=> $this->_getControllerUrl('edit')),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();
    }

    /**
     * Prepare massaction
     *
     * @return Enterprise_Rma_Block_Adminhtml_Rma_Grid
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('entity_ids');

        $this->getMassactionBlock()->addItem('status', array(
            'label'=> Mage::helper('Enterprise_Rma_Helper_Data')->__('Close'),
            'url'  => $this->getUrl($this->_getControllerUrl('close')),
            'confirm'  => Mage::helper('Enterprise_Rma_Helper_Data')->__("You have chosen to change status(es) of the selected RMA requests to Close.\nAre you sure you want to proceed?")
        ));

        return $this;
    }

    /**
     * Get Url to action
     *
     * @param  string $action action Url part
     * @return string
     */
    protected function _getControllerUrl($action = '')
    {
        return '*/*/' . $action;
    }

    /**
     * Retrieve row url
     *
     * @param $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl($this->_getControllerUrl('edit'), array(
            'id' => $row->getId()
        ));
    }
}
