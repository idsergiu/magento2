<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Gift wrapping order create items info block
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Order_Create_Items
    extends Enterprise_GiftWrapping_Block_Adminhtml_Order_Create_Abstract
{
    /**
     * Select element for choosing gift wrapping design
     *
     * @return array
     */
    public function getDesignSelectHtml()
    {
        $select = $this->getLayout()->createBlock('Mage_Core_Block_Html_Select')
            ->setData(array(
                'id'    => 'giftwrapping_design_item',
                'class' => 'select'
            ))
            ->setOptions($this->getDesignCollection()->toOptionArray());
        return $select->getHtml();
    }

    /**
     * Prepare and return quote items info
     *
     * @return Varien_Object
     */
    public function getItemsInfo()
    {
        $data = array();
        foreach ($this->getQuote()->getAllItems() as $item) {
            if ($item->getParentItem()) {
                continue;
            }
            if ($this->getDisplayGiftWrappingForItem($item)) {
                $temp = array();
                if ($price = $item->getProduct()->getGiftWrappingPrice()) {
                    if ($this->getDisplayWrappingBothPrices()) {
                        $temp['price_incl_tax'] = $this->calculatePrice(new Varien_Object(), $price, true);
                        $temp['price_excl_tax'] = $this->calculatePrice(new Varien_Object(), $price);
                    } else {
                        $temp['price'] = $this->calculatePrice(new Varien_Object(), $price,
                            $this->getDisplayWrappingPriceInclTax()
                        );
                    }
                }
                $temp['design'] = $item->getGwId();
                $data[$item->getId()] = $temp;
            }
        }
        return new Varien_Object($data);
    }

    /**
     * Check ability to display gift wrapping for items during backend order create
     *
     * @return bool
     */
    public function canDisplayGiftWrappingForItems()
    {
        $canDisplay = false;
        $count = count($this->getDesignCollection());
        if ($count) {
            foreach ($this->getQuote()->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($this->getDisplayGiftWrappingForItem($item)) {
                    $canDisplay = true;
                }
            }
        }
        return $canDisplay;
    }

    /**
     * Check ability to display gift wrapping for quote item
     *
     * @param Mage_Sales_Model_Quote_Item $item
     * @return bool
     */
    public function getDisplayGiftWrappingForItem($item)
    {
        $allowed = $item->getProduct()->getGiftWrappingAvailable();
        return Mage::helper('Enterprise_GiftWrapping_Helper_Data')
            ->isGiftWrappingAvailableForProduct($allowed, $this->getStoreId());
    }
}
