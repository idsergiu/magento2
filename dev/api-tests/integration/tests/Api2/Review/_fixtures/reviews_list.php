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
 * @category    Paas
 * @package     tests
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require realpath(dirname(__FILE__) . '/../../..') . '/Api/SalesOrder/_fixtures/product_simple.php';
require realpath(dirname(__FILE__) . '/../../..') . '/Api/SalesOrder/_fixtures/product_virtual.php';
require 'store.php';

/** @var $productSimple Mage_Catalog_Model_Product */
$productSimple = Magento_Test_Webservice::getFixture('product_simple');
/** @var $productVirtual Mage_Catalog_Model_Product */
$productVirtual = Magento_Test_Webservice::getFixture('product_virtual');
/** @var $store Mage_Core_Model_Store */
$store = Magento_Test_Webservice::getFixture('store');

$reviewsList = array();

/** @var $review Mage_Review_Model_Review */
$review = new Mage_Review_Model_Review();
$reviewData = require 'Frontend/ReviewData.php';
$reviewData['stores'] = array(Mage::app()->getDefaultStoreView()->getId());
$review->setData($reviewData);
$entityId = $review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE);

// Review #1: Simple Product, Status Approved
$review->setEntityId($entityId)
    ->setEntityPkValue($productSimple->getId())
    ->setStoreId($productSimple->getStoreId())
    ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
    ->save();

// Review #2: Simple Product, Status Approved
$review2 = new Mage_Review_Model_Review();
$review2->setData($reviewData)
    ->setEntityId($entityId)
    ->setEntityPkValue($productSimple->getId())
    ->setStoreId($store->getId())
    ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
    ->save();

// Review #3: Virtual Product, Status Approved, Custom Store
$reviewData['stores'] = array($store->getId());
$review3 = new Mage_Review_Model_Review();
$review3->setData($reviewData)
    ->setEntityId($entityId)
    ->setEntityPkValue($productVirtual->getId())
    ->setStoreId($productVirtual->getStoreId())
    ->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
    ->save();

$reviewsList[] = $review;
$reviewsList[] = $review2;
$reviewsList[] = $review3;

Magento_Test_Webservice::setFixture('reviews_list', $reviewsList);
