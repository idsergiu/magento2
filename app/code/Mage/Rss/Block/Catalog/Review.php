<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Rss
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Review form block
 *
 * @category   Mage
 * @package    Mage_Rss
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Rss_Block_Catalog_Review extends Mage_Core_Block_Abstract
{
    /**
     * Render XML response
     *
     * @return string
     */
    protected function _toHtml()
    {
        $newUrl = Mage::getUrl('rss/catalog/review');
        $title = Mage::helper('Mage_Rss_Helper_Data')->__('Pending product review(s)');
        Mage::helper('Mage_Rss_Helper_Data')->disableFlat();

        $rssObj = Mage::getModel('Mage_Rss_Model_Rss');
        $data = array(
            'title' => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
        );
        $rssObj->_addHeader($data);

        $reviewModel = Mage::getModel('Mage_Review_Model_Review');

        $collection = $reviewModel->getProductCollection()
            ->addStatusFilter($reviewModel->getPendingStatus())
            ->addAttributeToSelect('name', 'inner')
            ->setDateOrder();

        Mage::dispatchEvent('rss_catalog_review_collection_select', array('collection' => $collection));

        Mage::getSingleton('Mage_Core_Model_Resource_Iterator')->walk(
            $collection->getSelect(),
            array(array($this, 'addReviewItemXmlCallback')),
            array('rssObj'=> $rssObj, 'reviewModel'=> $reviewModel));
        return $rssObj->createRssXml();
    }

    /**
     * Format single RSS element
     *
     * @param array $args
     * @return null
     */
    public function addReviewItemXmlCallback($args)
    {
        $rssObj = $args['rssObj'];
        $row = $args['row'];

        $store = Mage::app()->getStore($row['store_id']);
        $urlModel = Mage::getModel('Mage_Core_Model_Url')->setStore($store);
        $productUrl = $urlModel->getUrl('catalog/product/view', array('id' => $row['entity_id']));
        $reviewUrl = Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl(
            'adminhtml/catalog_product_review/edit/',
            array('id' => $row['review_id'], '_secure' => true, '_nosecret' => true));
        $storeName = $store->getName();

        $description = '<p>'
                     . $this->__('Product: <a href="%s">%s</a> <br/>', $productUrl, $row['name'])
                     . $this->__('Summary of review: %s <br/>', $row['title'])
                     . $this->__('Review: %s <br/>', $row['detail'])
                     . $this->__('Store: %s <br/>', $storeName )
                     . $this->__('Click <a href="%s">here</a> to view the review.', $reviewUrl)
                     . '</p>';
        $data = array(
            'title'         => $this->__('Product: "%s" review By: %s', $row['name'], $row['nickname']),
            'link'          => 'test',
            'description'   => $description,
        );
        $rssObj->_addEntry($data);
    }
}
