<?php

class Mage_Catalog_Model_Admin_Search extends Varien_Object 
{
    public function load()
    {
        $arr = array();
        
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('description')
            ->addSearchFilter($this->getQuery())
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->loadData();
        
        foreach ($collection as $product) {
            $arr[] = array(
                'id'            => 'product/1/'.$product->getProductId(),
                'type'          => 'Product',
                'name'          => $product->getName(),
                'description'   => substr($product->getDescription(), 0, 50),
            );
        }
        
        $this->setResults($arr);
        
        return $this;
    }
}