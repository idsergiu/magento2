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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * API2 for products instance (Admin)
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Api2_Product_Rest_Admin_V1 extends Mage_Catalog_Model_Api2_Product_Rest
{
    /**
     * Product helper
     *
     * @var Mage_Catalog_Model_Api2_Helper
     */
    protected $_productResourceHelper;

    /**
     * Initialize product resource helper
     */
    function __construct()
    {
        $this->_productResourceHelper = Mage::getSingleton('catalog/api2_helper');
    }

    /**
     * Add special fields to product get response
     *
     * @param Mage_Catalog_Model_Product $product
     */
    protected function _prepareProductForResponse(Mage_Catalog_Model_Product $product)
    {
        $pricesFilterKeys = array('price_id', 'all_groups', 'website_price');
        $groupPrice = $product->getData('group_price');
        $product->setData('group_price', $this->_filterOutArrayKeys($groupPrice, $pricesFilterKeys, true));
        $tierPrice = $product->getData('tier_price');
        $product->setData('tier_price', $this->_filterOutArrayKeys($tierPrice, $pricesFilterKeys, true));

        $stockData = $product->getStockItem()->getData();
        $stockDataFilterKeys = array('item_id', 'product_id', 'stock_id', 'low_stock_date', 'type_id',
            'stock_status_changed_auto', 'stock_status_changed_automatically', 'product_name', 'store_id',
            'product_type_id', 'product_status_changed', 'product_changed_websites');
        $product->setData('stock_data', $this->_filterOutArrayKeys($stockData, $stockDataFilterKeys));
        $product->setData('product_type_name', $product->getTypeId());
    }

    /**
     * Remove specified keys from associative or indexed array
     *
     * @param array $array
     * @param array $keys
     * @param bool $dropOrigKeys if true - return array as indexed array
     * @return array
     */
    protected function _filterOutArrayKeys(array $array, array $keys, $dropOrigKeys = false)
    {
        $isIndexedArray = is_array(reset($array));
        if ($isIndexedArray) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $value = array_diff_key($value, array_flip($keys));
                }
            }
            if ($dropOrigKeys) {
                $array = array_values($array);
            }
            unset($value);
        } else {
            $array = array_diff_key($array, array_flip($keys));
        }

        return $array;
    }

    /**
     * Retrieve list of products
     *
     * @return array
     */
    protected function _retrieveCollection()
    {
        /** @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection');
        $store = $this->_getStore();
        $collection->setStoreId($store->getId());
        $collection->addAttributeToSelect(array_keys(
            $this->getAvailableAttributes($this->getUserType(), Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ)
        ));
        $this->_applyCategoryFilter($collection);
        $this->_applyCollectionModifiers($collection);
        $products = $collection->load()->toArray();
        return $products;
    }

    /**
     * Delete product by its ID
     *
     * @throws Mage_Api2_Exception
     */
    protected function _delete()
    {
        $product = $this->_getProduct();
        try {
            $product->delete();
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_INTERNAL_ERROR);
        }
    }

    /**
     * Create product
     *
     * @param array $data
     * @return string
     */
    protected function _create(array $data)
    {
        /* @var $validator Mage_Catalog_Model_Api2_Product_Validator_Product */
        $validator = Mage::getModel('catalog/api2_product_validator_product', array(
            'operation' => self::OPERATION_CREATE
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }

        $type = $data['type_id'];
        if ($type !== 'simple') {
            $this->_critical("Creation of products with type '$type' is not implemented",
                Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED);
        }
        $set = $data['attribute_set_id'];
        $sku = $data['sku'];

        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->setAttributeSetId($set)
            ->setTypeId($type)
            ->setSku($sku);

        $this->_productResourceHelper->prepareDataForSave($product, $data);
        try {
            $product->validate();
            $product->save();
            $this->_multicall($product->getId());
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }

        return $this->_getLocation($product);
    }

    /**
     * Update product by its ID
     *
     * @param array $data
     */
    protected function _update(array $data)
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_getProduct();
        /* @var $validator Mage_Catalog_Model_Api2_Product_Validator_Product */
        $validator = Mage::getModel('catalog/api2_product_validator_product', array(
            'operation' => self::OPERATION_UPDATE,
            'product'   => $product
        ));

        if (!$validator->isValidData($data)) {
            foreach ($validator->getErrors() as $error) {
                $this->_error($error, Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
            }
            $this->_critical(self::RESOURCE_DATA_PRE_VALIDATION_ERROR);
        }
        if (isset($data['sku'])) {
            $product->setSku($data['sku']);
        }
        $this->_productResourceHelper->setIsUpdate(true)->prepareDataForSave($product, $data);
        try {
            $product->validate();
            $product->save();
        } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
            $this->_critical(sprintf('Invalid attribute "%s": %s', $e->getAttributeCode(), $e->getMessage()),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        } catch (Mage_Core_Exception $e) {
            $this->_critical($e->getMessage(), Mage_Api2_Model_Server::HTTP_INTERNAL_ERROR);
        } catch (Exception $e) {
            $this->_critical(self::RESOURCE_UNKNOWN_ERROR);
        }
    }
}
