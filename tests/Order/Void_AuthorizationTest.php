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
 * Void Authorizations
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Order_Void_AuthorizationTest extends Mage_Selenium_TestCase
{

    /**
     * <p>Preconditions:</p>
     *
     * <p>Log in to Backend.</p>
     * <p>Navigate to 'System Configuration' page</p>
     * <p>Enable all shipping methods</p>
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreConditions()
    {}

    /**
     * @test
     */
    public function createProducts()
    {
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        $this->addParameter('id', '0');
        $productData = $this->loadData('simple_product_for_order', NULL, array('general_name', 'general_sku'));
        $this->productHelper()->createProduct($productData);
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        return $productData;
    }


    /**
     * <p>PayPal Direct. Void</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'PayPal Direct - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Void Order.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Void successful</p>
     *
     * @depends createProducts
     * @test
     */
    public function payPalDirect($productData)
    {
        //Preconditions: Enabling PayPal
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('paypal_enable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
        //Preconditions: Enabling Website payments pro
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('website_payments_pro_wo_3d_enable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $orderData = $this->loadData('order_data_website_payments_pro_1');
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData['general_sku'];
        $orderId = $this->orderHelper()->createOrder($orderData);
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $this->searchAndOpen(array('1' => $orderId), TRUE, 'sales_order_grid');
        $this->assertTrue($this->buttonIsPresent('void'), 'Button Void is not on the page');
        $this->clickButtonAndConfirm('void', 'confirmation_to_void');
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->assertTrue($this->successMessage('success_void'), $this->messages);
        //Postconditions
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('website_payments_pro_wo_3d_disable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
    }

    /**
     * <p>PayPalUK Direct. Void</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'PayPalUkDirect - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Void Order.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Void is successful</p>
     *
     * @depends createProducts
     * @test
     */
    public function payPalUKDirect($productData)
    {
        //Preconditions: Enabling PayPal
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('paypal_enable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
        //Preconditions: Enabling PayPalUKDirect
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $paypalukdirect = $this->loadData('paypal_uk_direct_wo_3d_enable');
        $this->fillForm($paypalukdirect, 'sales_paypal');
        $this->saveForm('save_config');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $orderData = $this->loadData('order_data_paypal_direct_payment_payflow_edition_1');
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData['general_sku'];
        $orderId = $this->orderHelper()->createOrder($orderData);
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $this->searchAndOpen(array('1' => $orderId), TRUE, 'sales_order_grid');
        $this->assertTrue($this->buttonIsPresent('void'), 'Button Void is not on the page');
        $this->clickButtonAndConfirm('void', 'confirmation_to_void');
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->assertTrue($this->successMessage('success_void'), $this->messages);
        //Postconditions
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('paypal_uk_direct_wo_3d_disable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
    }

    /**
     * <p>TL-MAGE-1210:Verisign. Void</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'Verisign - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Void.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Void successful</p>
     *
     * @depends createProducts
     * @test
     */
    public function payFlowProVerisign($productData)
    {
        //Preconditions: Enabling PayPal
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('paypal_enable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
        //Preconditions: Enabling PayflowPro
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('payflow_pro_wo_3d_enable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $orderData = $this->loadData('order_data_payflow_pro_1');
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData['general_sku'];
        $orderId = $this->orderHelper()->createOrder($orderData);
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $this->searchAndOpen(array('1' => $orderId), TRUE, 'sales_order_grid');
        $this->assertTrue($this->buttonIsPresent('void'), 'Button Void is not on the page');
        $this->clickButtonAndConfirm('void', 'confirmation_to_void');
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->assertTrue($this->successMessage('success_void'), $this->messages);
        //Postconditions
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/paypal/');
        $this->clickControl('tab', 'sales_paypal');
        $payment = $this->loadData('payflow_pro_wo_3d_disable');
        $this->fillForm($payment, 'sales_paypal');
        $this->saveForm('save_config');
    }

    /**
     * With Bug. Void button is not present on the page.
     * <p>AuthorizeNet. Void</p>
     * <p>Steps:</p>
     * <p>1.Go to Sales-Orders.</p>
     * <p>2.Press "Create New Order" button.</p>
     * <p>3.Press "Create New Customer" button.</p>
     * <p>4.Choose 'Main Store' (First from the list of radiobuttons) if exists.</p>
     * <p>5.Fill all fields.</p>
     * <p>6.Press 'Add Products' button.</p>
     * <p>7.Add first two products.</p>
     * <p>8.Choose shipping address the same as billing.</p>
     * <p>9.Check payment method 'AuthorizeNet - Visa'</p>
     * <p>10. Fill in all required fields.</p>
     * <p>11.Choose first from 'Get shipping methods and rates'.</p>
     * <p>12.Submit order.</p>
     * <p>13.Void.</p>
     * <p>Expected result:</p>
     * <p>New customer is created. Order is created for the new customer. Void is successful</p>
     *
     * @depends createProducts
     * @test
     */
    public function authorizeNet($productData)
    {
        //Preconditions
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/payment/');
        $this->clickControl('tab', 'sales_payment_methods');
        $payment = $this->loadData('authorize_net_without_3d_enable');
        $this->fillForm($payment, 'sales_payment_methods');
        $this->saveForm('save_config');
        //Steps
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $orderData = $this->loadData('order_data_authorize_net_1');
        $orderData['products_to_add']['product_1']['filter_sku'] = $productData['general_sku'];
        $orderId = $this->orderHelper()->createOrder($orderData);
        $this->navigate('manage_sales_orders');
        $this->assertTrue($this->checkCurrentPage('manage_sales_orders'), $this->messages);
        $this->searchAndOpen(array('1' => $orderId), TRUE, 'sales_order_grid');
        $this->assertTrue($this->buttonIsPresent('void'), 'Button Void is not on the page');
        $this->clickButtonAndConfirm('void', 'confirmation_to_void');
        $this->addParameter('id', $this->defineIdFromUrl());
        $this->assertTrue($this->successMessage('success_void'), $this->messages);
        //Postconditions
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->addParameter('tabName', 'edit/section/payment_services/');
        $this->clickControl('tab', 'sales_payment_methods');
        $payment = $this->loadData('authorize_net_without_3d_disable');
        $this->fillForm($payment, 'sales_payment_methods');
        $this->saveForm('save_config');
    }
}
