<?php
/**
 * Product attributes form
 *
 * @package    Ecom
 * @subpackage Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Mage_Catalog_Block_Admin_Product_Card extends Mage_Core_Block_Abstract
{
    protected $_productId;
    protected $_attributeSet;
    
    public function __construct() 
    {
        $this->_productId   = (int) Mage_Core_Controller::getController()->getRequest()->getParam('product', false);
        $this->_attributeSet= (int) Mage_Core_Controller::getController()->getRequest()->getParam('setid', false);
    }
    
    public function toJson()
    {
        $setCollection  = Mage::getModel('catalog', 'product_attribute_set_collection');
        $setCollection->load();
        $arrSets = $setCollection->__toArray();
        
        // Get first sttributes set id
        if (!$this->_attributeSet) {
            if (isset($arrSets['items'][0])) {
                $this->_attributeSet = $arrSets['items'][0]['product_attribute_set_id'];
            }
            else {
                Mage::exception('Undefined attributes set id');
            }
        }
        
        // Declare set attributes
        $set = Mage::getModel('catalog', 'product_attribute_set');
        //$arrAtributes = $set->getAttributes($this->_attributeSet);
        $arrGroups = $set->getGroups($this->_attributeSet);
        
        // Declare attributes groups
        /*$groupCollection= Mage::getModel('catalog', 'product_attribute_group_collection');
        $groupCollection->distinct(true);
        $groupCollection->addAttributeFilter($arrAtributes);
        $arrGroups = $groupCollection->load()->__toArray();*/
        
        // Create card JSON structure
        $cardStructure = array();
        $cardStructure['attribute_set'] = $arrSets;
        $cardStructure['tabs'] = array();
        
        // Tabs description JSON
        $baseTabUrl = Mage::getBaseUrl().'/mage_catalog/product/form/';
        if ($this->_productId) {
            $baseTabUrl.= 'product/' . $this->_productId . '/';
        }
        
        foreach ($arrGroups as $group) {
            $url = $baseTabUrl . 'group/' . $group['product_attribute_group_id'].'/';
            $url.= 'set/'.$this->_attributeSet.'/';
            $cardStructure['tabs'][] = array(
                'name'  => $group['product_attribute_group_code'],
                'url'   => $url,
                'title' => $group['product_attribute_group_code'],
            );
        }

        $cardStructure['tabs'][] = array(
            'name'  => 'related',
            'url'   => Mage::getBaseUrl().'/mage_catalog/product/relatedProducts/',
            'title' => 'Related products',
        );
        
        // Set first tab as active
        $cardStructure['tabs'][0]['active'] = true;
        $cardStructure['tabs'][0]['url']    = $cardStructure['tabs'][0]['url'] . 'isdefault/1/';
        return Zend_Json::encode($cardStructure);
    }
}