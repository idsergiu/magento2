<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * API2 attributes grid block
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Block_Adminhtml_Attribute_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Set grid ID
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->setId('api2_attributes');
    }

    /**
     * Collection object set up
     */
    protected function _prepareCollection()
    {
        $collection = new Varien_Data_Collection();

        foreach (Mage_Api2_Model_Auth_User::getUserTypes() as $type => $label) {
            $collection->addItem(
                new Varien_Object(array('user_type_name' => $label, 'user_type_code' => $type))
            );
        }

        $this->setCollection($collection);
    }

    /**
     * Prepare grid columns
     *
     * @return Mage_Api2_Block_Adminhtml_Attribute_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn('user_type_name', array(
            'header'    => $this->__('User Type'),
            'index'     => 'user_type_name'
        ));

        return parent::_prepareColumns();
    }

    /**
     * Disable unnecessary functionality
     *
     * @return Mage_Api2_Block_Adminhtml_Attribute_Grid
     */
    public function _prepareLayout()
    {
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return $this;
    }

    /**
     * Get row URL
     *
     * @param Varien_Object $row
     * @return string|null
     */
    public function getRowUrl($row)
    {
        /** @var $session Mage_Backend_Model_Auth_Session */
        $session = Mage::getSingleton('Mage_Backend_Model_Auth_Session');
        if ($session->isAllowed('Mage_Api2::rest_attributes_edit')) {
            return $this->getUrl('*/*/edit', array('type' => $row->getUserTypeCode()));
        }

        return null;
    }
}
