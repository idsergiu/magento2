<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Catalog layer filter abstract
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Catalog_Block_Layer_Filter_Abstract extends Mage_Core_Block_Template
{
    /**
     * Catalog Layer Filter Attribute model
     *
     * @var Mage_Catalog_Model_Layer_Filter_Attribute
     */
    protected $_filter;

    /**
     * Filter Model Name
     *
     * @var string
     */
    protected $_filterModelName;

    /**
     * Initialize filter template
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('Mage_Catalog::layer/filter.phtml');
    }

    /**
     * Initialize filter model object
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    public function init()
    {
        $this->_initFilter();
        return $this;
    }

    /**
     * Init filter model object
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    protected function _initFilter()
    {
        if (!$this->_filterModelName) {
            Mage::throwException(Mage::helper('Mage_Catalog_Helper_Data')->__('Filter model name must be declared.'));
        }
        $this->_filter = Mage::getModel($this->_filterModelName)
            ->setLayer($this->getLayer());
        $this->_prepareFilter();

        $this->_filter->apply($this->getRequest(), $this);
        return $this;
    }

    /**
     * Prepare filter process
     *
     * @return Mage_Catalog_Block_Layer_Filter_Abstract
     */
    protected function _prepareFilter()
    {
        return $this;
    }

    /**
     * Retrieve name of the filter block
     *
     * @return string
     */
    public function getName()
    {
        return $this->_filter->getName();
    }

    /**
     * Retrieve filter items
     *
     * @return array
     */
    public function getItems()
    {
        return $this->_filter->getItems();
    }

    /**
     * Retrieve filter items count
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->_filter->getItemsCount();
    }

    /**
     * Retrieve block html
     *
     * @return string
     */
    public function getHtml()
    {
        return parent::_toHtml();
    }
}
