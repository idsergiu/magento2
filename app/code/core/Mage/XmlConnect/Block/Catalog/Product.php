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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product data xml renderer
 *
 * @category   Mage
 * @package    Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_XmlConnect_Block_Catalog_Product extends Mage_XmlConnect_Block_Catalog
{

    /**
     * Product view small image size
     */
    const PRODUCT_IMAGE_SMALL_RESIZE_PARAM  = 70;

    /**
     * Product view big image size
     */
    const PRODUCT_IMAGE_BIG_RESIZE_PARAM    = 130;

    /**
     * Retrieve product attributes as xml object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param string $itemNodeName
     *
     * @return Varien_Simplexml_Element
     */
    public function productToXmlObject(Mage_Catalog_Model_Product $product, $itemNodeName = 'item')
    {
        $item = new Varien_Simplexml_Element('<' . $itemNodeName . '></' . $itemNodeName . '>');
        if ($product->getId()) {
            $item->addChild('entity_id', $product->getId());
            $item->addChild('name', $item->xmlentities(strip_tags($product->getName())));
            $item->addChild('entity_type', $product->getTypeId());
            $item->addChild('description', $item->xmlentities($product->getDescription()));

            $icon = clone Mage::helper('catalog/image')->init($product, 'image')
                ->resize($itemNodeName == 'item' ? self::PRODUCT_IMAGE_SMALL_RESIZE_PARAM : self::PRODUCT_IMAGE_BIG_RESIZE_PARAM);
            $item->addChild('icon', $icon);
            $item->addChild('in_strock', (int)$product->isInStock());
            /**
             * By default all products has gallery (because of collection not load gallery attribute)
             */
            $hasGallery = 1;
            if ($product->getMediaGalleryImages()) {
                $hasGallery = sizeof($product->getMediaGalleryImages()) > 0 ? 1 : 0;
            }
            $item->addChild('has_gallery', $hasGallery);
            /**
             * If product type is grouped than it has options as its grouped items
             */
            if ($product->getTypeId() == Mage_Catalog_Model_Product_Type_Grouped::TYPE_CODE) {
                $product->setHasOptions(true);
            }
            $item->addChild('has_options', (int)$product->getHasOptions());
            $item->addChild('is_salable', (int)$product->isSaleable());

            if (!$product->getRatingSummary()) {
                Mage::getModel('review/review')
                   ->getEntitySummary($product, Mage::app()->getStore()->getId());
            }

            $item->addChild('rating_summary', round((int)$product->getRatingSummary()->getRatingSummary() / 10));
            $item->addChild('reviews_count', $product->getRatingSummary()->getReviewsCount());

            if ($this->getChild('product_price')) {
                $this->getChild('product_price')->setProduct($product)
                   ->setProductXmlObj($item)
                   ->collectProductPrices();
            }
        }

        return $item;
    }

    /**
     * Render product info xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($this->getRequest()->getParam('id', 0));

        $this->setProduct($product);
        $productXmlObj = $this->productToXmlObject($product, 'product');

        $relatedProductsBlock = $this->getChild('related_products');
        if ($relatedProductsBlock) {
            $relatedXmlObj = $relatedProductsBlock->getRelatedProductsXmlObj();
            $productXmlObj->appendChild($relatedXmlObj);
        }
        return $productXmlObj->asNiceXml();
    }

}
