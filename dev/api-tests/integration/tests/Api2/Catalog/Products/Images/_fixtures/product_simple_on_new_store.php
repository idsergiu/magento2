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
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require dirname(dirname(dirname(__FILE__))).'/Categories/_fixtures/new_category_on_new_store.php';

$fixturesDir = realpath(dirname(__FILE__) . '/../../../../../../fixtures');

/* @var $productFixture Mage_Catalog_Model_Product */
$product = require $fixturesDir . '/Catalog/Product.php';
$product->setStoreId(0)
    ->setWebsiteIds(array(Mage::app()->getDefaultStoreView()->getWebsiteId()))
    ->save();
// product should be assigned to website (with appropriate store view) to use store view in rest
$websites = $product->getWebsiteIds();
$websites[] = Magento_Test_Webservice::getFixture('website')->getId();

// to make stock item visible from created product it should be reloaded
$product = Mage::getModel('catalog/product')->load($product->getId());
$product->setStoreId(Magento_Test_Webservice::getFixture('store')->getId())
    ->setWebsiteIds($websites)
    ->save();
Magento_Test_Webservice::setFixture('product_simple', $product);
