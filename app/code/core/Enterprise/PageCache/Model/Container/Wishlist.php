<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Wishlist sidebar container
 */
class Enterprise_PageCache_Model_Container_Wishlist extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get identifier from cookies
     *
     * @return string
     */
    protected function _getIdentifier()
    {
        return $this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_WISHLIST, '')
            . ($this->_getCookieValue(Enterprise_PageCache_Model_Cookie::COOKIE_CUSTOMER_GROUP, ''));
    }

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return 'CONTAINER_WISHLIST_' . md5($this->_placeholder->getAttribute('cache_id') . $this->_getIdentifier());
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $block = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $block;
        $block->setTemplate($template);

        $blockPrice = new Mage_Catalog_Block_Product_Price_Template();
        $blockPrice->addPriceBlockType('msrp','Mage_Catalog_Block_Product_Price','catalog/product/price_msrp.phtml');

        $layout = Mage::app()->getLayout();
        $layout->addBlock($blockPrice,'catalog_product_price_template');

        $block->setLayout($layout);
        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));

        return $block->toHtml();
    }
}
