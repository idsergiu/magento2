<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Eav
 * @copyright   {copyright}
 * @license     {license_link}
 */


abstract class Mage_Eav_Model_Convert_Parser_Abstract
    extends Mage_Dataflow_Model_Convert_Parser_Abstract
{
    protected $_storesById;
    protected $_attributeSetsById;
    protected $_attributeSetsByName;

    public function getStoreIds($stores)
    {
       if (empty($stores)) {
            $storeIds = array(0);
        } else {
            $storeIds = array();
            foreach (explode(',', $stores) as $store) {
                if (is_numeric($store)) {
                    $storeIds[] = $store;
                } else {
                    $storeNode = Mage::getConfig()->getNode('stores/'.$store);
                    if (!$storeNode) {
                        return false;
                    }
                    $storeIds[] = (int)$storeNode->system->store->id;
                }
            }
        }
        return $storeIds;
    }

    public function getStoreCode($storeId)
    {
        return Mage::app()->getStore($storeId?$storeId:0)->getCode();
    }

    public function loadAttributeSets($entityTypeId)
    {
        $attributeSetCollection = Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
            ->setEntityTypeFilter($entityTypeId)
            ->load();
        $this->_attributeSetsById = array();
        $this->_attributeSetsByName = array();
        foreach ($attributeSetCollection as $id=>$attributeSet) {
            $name = $attributeSet->getAttributeSetName();
            $this->_attributeSetsById[$id] = $name;
            $this->_attributeSetsByName[$name] = $id;
        }
        return $this;
    }

    public function getAttributeSetName($entityTypeId, $id)
    {
        if (!$this->_attributeSetsById) {
            $this->loadAttributeSets($entityTypeId);
        }
        return isset($this->_attributeSetsById[$id]) ? $this->_attributeSetsById[$id] : false;
    }

    public function getAttributeSetId($entityTypeId, $name)
    {
        if (!$this->_attributeSetsByName) {
            $this->loadAttributeSets($entityTypeId);
        }
        return isset($this->_attributeSetsByName[$name]) ? $this->_attributeSetsByName[$name] : false;
    }

    public function getSourceOptionId(Mage_Eav_Model_Entity_Attribute_Source_Interface $source, $value)
    {
        foreach ($source->getAllOptions() as $option) {
            if (strcasecmp($option['label'], $value)==0) {
                return $option['value'];
            }
        }
        return null;
    }
}
