<?php

class Mage_Sales_Model_Mysql4_Quote_Address_Collection extends Varien_Data_Collection_Db
{
    static protected $_addressTable = null;

    public function __construct() 
    {
        parent::__construct(Mage::registry('resources')->getConnection('sales_read'));
        self::$_addressTable = Mage::registry('resources')->getTableName('sales', 'quote_address');
        $this->_sqlSelect->from(self::$_addressTable);
        $this->setItemObjectClass(Mage::getConfig()->getModelClassName('sales', 'quote_address'));
    }
    
    public function loadByQuoteId($quoteId)
    {
        $this->addFilter('quote_id', (int)$quoteId, 'and');
        $this->load();
        return $this;
    }
        
    public function getByAddressId($addressId)
    {
        foreach ($this->getItems() as $item) {
            if ($item->getQuoteAddressId()==$addressId) {
                return $item;
            }
        }
    }
}