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
 * @package    Mage_Reports
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Reports quote collection
 *
 * @category   Mage
 * @package    Mage_Reports
 * @author     Dmytro Vasylenko  <dimav@varien.com>
 */
class Mage_Reports_Model_Mysql4_Quote_Collection extends Mage_Sales_Model_Entity_Quote_Collection
{
    public function setActiveFilter()
    {
        $this->addAttributeToFilter('is_active', '1');
        return $this;
    }

    public function addCustomerName()
    {
        $this->joinAttribute('customer_firstname', 'customer/firstname', 'customer_id')
            ->joinAttribute('customer_lastname', 'customer/lastname', 'customer_id')
            ->addExpressionAttributeToSelect(
                'customer_name',
                'CONCAT({{customer_firstname}}, " ", {{customer_lastname}})',
                array('customer_firstname', 'customer_lastname'));

        return $this;
    }

    public function addCustomerEmail()
    {
        $this->joinAttribute('customer_email', 'customer/email', 'customer_id');
        return $this;
    }

    public function addQuoteItems()
    {
        $quoteItem = Mage::getResourceSingleton('sales/quote_item');
        /* @var $quoteItem Mage_Sales_Model_Entity_Quote_Item */

        $this->getSelect()
            ->joinLeft(array('quote_items' => $quoteItem->getEntityTable()),
                "quote_items.parent_id=e.entity_id AND quote_items.entity_type_id=".$quoteItem->getTypeId(),
                array());

        $attr = $quoteItem->getAttribute('qty');
        $attrId = $attr->getAttributeId();
        $attrTableName = $attr->getBackend()->getTable();
        $attrFieldName = $attr->getBackend()->isStatic() ? 'qty' : 'value';

        $this->getSelect()
            ->joinInner(array('quote_items_qty' => $attrTableName),
                "quote_items_qty.entity_id=quote_items.entity_id AND quote_items_qty.attribute_id=".$attrId,
                 array())
            ->from("", array(
                "items" => "COUNT(quote_items.entity_id)",
                "items_qty" => "SUM(quote_items_qty.{$attrFieldName})"))
            ;//->having('items > 0');

        return $this;
    }

    public function addSubtotal($storeIds = '')
    {
        $quoteAddress = Mage::getResourceSingleton('sales/quote_address');
        /* @var $quoteItem Mage_Sales_Model_Entity_Quote_Address */

        $this->getSelect()
            ->joinLeft(array('quote_addr' => $quoteAddress->getEntityTable()),
                "quote_addr.parent_id=e.entity_id AND quote_addr.entity_type_id=".$quoteAddress->getTypeId(),
                array());

        $attr = $quoteAddress->getAttribute('base_subtotal_with_discount');
        $attrId = $attr->getAttributeId();
        $attrTableName = $attr->getBackend()->getTable();
        $attrFieldName = $attr->getBackend()->isStatic() ? 'base_subtotal_with_discount' : 'value';

        $this->getSelect()
            ->joinLeft(array('quote_addr_subtotal' => $attrTableName),
                "quote_addr_subtotal.entity_id=quote_addr.entity_id AND quote_addr_subtotal.attribute_id=".$attrId,
                 array());
        if ($storeIds == '') {
            $rate = $this->getEntity()->getAttribute('store_to_base_rate');
            $rateId = $rate->getAttributeId();
            $rateTableName = $rate->getBackend()->getTable();
            $rateFieldName = $rate->getBackend()->isStatic() ? 'store_to_base_rate' : 'value';

            $this->getSelect()
                ->joinLeft(array('quote_rate' => $rateTableName),
                    "quote_rate.entity_id=e.entity_id AND quote_rate.attribute_id=".$rateId,
                     array());
            $this->getSelect()->from("", array("subtotal" => "SUM(IFNULL(quote_addr_subtotal.{$attrFieldName}/quote_rate.{$rateFieldName}, 0))"));
        } else {
            $this->getSelect()->from("", array("subtotal" => "SUM(IFNULL(quote_addr_subtotal.{$attrFieldName}, 0))"));
        }

        return $this;
    }

    public function getSelectCountSql()
    {
        $countSelect = clone $this->getSelect();
        $countSelect->reset(Zend_Db_Select::ORDER);
        $countSelect->reset(Zend_Db_Select::LIMIT_COUNT);
        $countSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $countSelect->reset(Zend_Db_Select::COLUMNS);
        $countSelect->reset(Zend_Db_Select::GROUP);
        $countSelect->from("", "count(DISTINCT e.entity_id)");
        $sql = $countSelect->__toString();
        return $sql;
    }
}
