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
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Prices Validation on the frontend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Tax_TaxAndPricesValidationFrontendTest extends Mage_Selenium_TestCase
{

    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {

//        $this->addParameter('productUrl', '');
//        $this->addParameter('productName', '');
    }

    /**
     * Create Customer for tests
     *
     * @test
     */
    public function createCustomer()
    {
        //Data
        $userData = $this->loadData('customer_account_for_prices_validation', NULL, 'email');
        $addressData = $this->loadData('customer_account_address_for_prices_validation');
        //Steps
        $this->navigate('manage_customers');
        $this->customerHelper()->createCustomer($userData, $addressData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_customer'), $this->messages);
        $customer = array('email' => $userData['email'], 'password' => $userData['password']);
        return $customer;
    }

    /**
     * Create category
     *
     * @test
     */
    public function createCategory()
    {
        $this->navigate('manage_categories');
        //Data
        $rootCat = 'Default Category';
        $categoryData = $this->loadData('sub_category_required', null, 'name');
        //Steps
        $this->categoryHelper()->createSubCategory($rootCat, $categoryData);
        //Verifying
        $this->assertTrue($this->successMessage('success_saved_category'), $this->messages);

        return $rootCat . '/' . $categoryData['name'];
    }

    /**
     * Create Simple Products for tests
     *
     * @depends createCategory
     * @test
     */
    public function createProducts($category)
    {
        $products = array();
        $this->navigate('manage_products');
        for ($i = 1; $i <= 1; $i++) {
            $simpleProductData = $this->loadData('simple_product_for_prices_validation_' . $i,
                    array('categories' => $category), array('general_name', 'general_sku'));
            $products[$i] = $simpleProductData['general_name'];
            $this->productHelper()->createProduct($simpleProductData);
            $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        }

        return $products;
    }

    /**
     * Create Order on the backend and validate prices with taxes
     *
     * @dataProvider dataSystemConfiguration
     * @depends createCustomer
     * @depends createProducts
     * @depends createCategory
     *
     * @test
     */
    public function validateTaxFrontend($dataProv, $customer, $products, $category)
    {
        //Data
        $category = substr($category, strpos($category, '/') + 1);
        $checkoutData = $this->loadData('checkout_data');
        //Preconditions
//        $this->navigate('system_configuration');
//        $this->systemConfigurationHelper()->configure($dataProv);
        $this->customerHelper()->frontLoginCustomer($customer);
        //Verify and add products to shopping cart
        foreach ($products as $key => $productName) {
            $priceInCategory = $this->loadData($dataProv . '_frontend_price_in_category_simple_' . $key,
                    array('product_name' => $productName, 'category' => $category));
            $priceInProdDetails = $this->loadData($dataProv . '_frontend_price_in_prod_details_simple_' . $key,
                    array('product_name' => $productName));
            $this->categoryHelper()->frontValidateProductInCategory($priceInCategory);
            $this->productHelper()->frontOpenProduct($productName);
            $this->categoryHelper()->frontVerifyProductPricesInCategory($productName,
                    $priceInProdDetails['verification'], 'product_page');
            $this->productHelper()->frontAddProductToCart();
        }
        //
//        $priceTotal = $this->shoppingCartHelper()->frontEstimateShipping('estimate_shipping', 'fixed');
//        $this->shoppingCartHelper()->verifyPricesDataOnPage(
//                'unit_cat_ex_ship_ex_frontend_price_in_shopping_cart_simple_products',
//                'unit_price_excl_shipping_excl_order');
//        $this->checkoutOnePageHelper()->createCheckout('checkout_data');
//        $this->checkoutOnePageHelper()->frontVerifyCheckoutData(
//                'unit_cat_ex_ship_ex_frontend_price_in_shopping_cart_simple_products',
//                'unit_price_excl_shipping_excl_order');
//        $this->clickButton('place_order');
//        $orderId = $this->_getControlXpath('link', 'order_number')->$this->getText();
//        $this->addParameter('orderId', $orderId);
//        $this->clickControl('link', 'order_number');
//        $this->$this->shoppingCartHelper()->verifyPricesDataOnPage(
//                'unit_cat_ex_ship_ex_frontend_price_in_shopping_cart_simple_products',
//                'unit_price_excl_shipping_excl_order');
    }

    public function dataSystemConfiguration()
    {
        return array(
            array('unit_cat_ex_ship_ex'),
//            array('row_cat_ex_ship_ex'),
//            array('total_cat_ex_ship_ex'),
//            array('unit_cat_ex_ship_in'),
//            array('row_cat_ex_ship_in'),
//            array('total_cat_ex_ship_in'),
//            array('unit_cat_in_ship_ex'),
//            array('row_cat_in_ship_ex'),
//            array('total_cat_in_ship_ex'),
//            array('unit_cat_in_ship_in'),
//            array('row_cat_in_ship_in'),
//            array('total_cat_in_ship_in')
        );
    }

}
