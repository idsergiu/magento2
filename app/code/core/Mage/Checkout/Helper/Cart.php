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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
 
/**
 * Shopping cart helper
 *
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Checkout_Helper_Cart extends Mage_Core_Helper_Url
{
    protected $_itemCount;
    
    /**
     * Retrieve url for add product to cart
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  string
     */
    public function getAddUrl($product)
    {
        return $this->_getUrl('checkout/cart/add', array(
            'product'=>$product->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL  => $this->getCurrentBase64Url()
        ));
    }
    
    /**
     * Retrieve url for remove product from cart
     *
     * @param   Mage_Sales_Quote_Item $item
     * @return  string
     */
    public function getRemoveUrl($item)
    {
        $params = array(
            'id'=>$item->getId(),
            Mage_Core_Controller_Front_Action::PARAM_NAME_BASE64_URL => $this->getCurrentBase64Url()
        );
        return $this->_getUrl('checkout/cart/delete', $params);
    }
    
    public function getCartUrl()
    {
        return $this->_getUrl('checkout/cart');
    }
    
    public function getLastItems()
    {
        
    }
    
    public function getItemCollection()
    {
        
    }

    public function getItemCount()
    {
        if (is_null($this->_itemCount)) {
            $quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
            $this->_itemCount = Mage::getResourceModel('checkout/cart')->fetchItemsSummaryQty($quoteId);
        }
        return $this->_itemCount;
    }
}
