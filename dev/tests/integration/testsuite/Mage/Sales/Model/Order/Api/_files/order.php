<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license {license_link}
 */
//Set up customer fixture
require 'customer.php';
/** @var $customer Mage_Customer_Model_Customer */
$customer = Mage::registry('customer');
//Set up virtual product fixture
require 'product_virtual.php';
/** @var $product Mage_Catalog_Model_Product */
$product = Mage::registry('product_virtual');

//Create quote
$quote = Mage::getModel('Mage_Sales_Model_Quote');
$quote->setStoreId(1)
    ->setCustomerEmail($customer->getEmail())
    ->setIsActive(false)
    ->setIsMultiShipping(false)
    ->assignCustomerWithAddressChange($customer)
    ->setCheckoutMethod($customer->getMode())
    ->setPasswordHash($customer->encryptPassword($customer->getPassword()))
    ->addProduct($product->load($product->getId()), 2);

$quote->collectTotals();
$quote->save();
Mage::register('quote', $quote);

//Create order
$quoteService = new Mage_Sales_Model_Service_Quote($quote);
//Set payment method to check/money order
$quoteService->getQuote()->getPayment()->setMethod('checkmo');
$order = $quoteService->submitOrder();
$order->place();
$order->save();
Mage::register('order', $order);

//Create order
$quote2 = Mage::getModel('Mage_Sales_Model_Quote');
$quote2->setStoreId(1)
    ->setCustomerEmail($customer->getEmail())
    ->setIsActive(false)
    ->setIsMultiShipping(false)
    ->assignCustomerWithAddressChange($customer)
    ->setCheckoutMethod($customer->getMode())
    ->setPasswordHash($customer->encryptPassword($customer->getPassword()))
    ->addProduct($product->load($product->getId()), 2);

$quote2->collectTotals();
$quote2->save();
Mage::register('quote2', $quote2);

$quoteService2 = new Mage_Sales_Model_Service_Quote($quote2);
//Set payment method to check/money order
$quoteService2->getQuote()->getPayment()->setMethod('checkmo');
$order2 = $quoteService2->submitOrder();
$order2->place();
$order2->save();
Mage::register('order2', $order2);
