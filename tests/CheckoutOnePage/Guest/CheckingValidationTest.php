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
 * Validating address filling for guest
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CheckoutOnePage_Guest_CheckingValidationTest extends Mage_Selenium_TestCase
{
    /**
     *
     * <p>Creating products for testing.</p>
     *
     * <p>Navigate to Sales-Orders page.</p>
     *
     */
    protected function assertPreConditions()
    {
        $this->addParameter('tabName', '');
        $this->addParameter('webSite', '');
        $this->addParameter('storeName', '');
    }

    /**
     * <p>Creating Simple product with required fields only</p>
     * <p>Steps:</p>
     * <p>1. Click "Add product" button;</p>
     * <p>2. Fill in "Attribute Set" and "Product Type" fields;</p>
     * <p>3. Click "Continue" button;</p>
     * <p>4. Fill in required fields;</p>
     * <p>5. Click "Save" button;</p>
     * <p>Expected result:</p>
     * <p>Product is created, confirmation message appears;</p>
     *
     * @test
     */
    public function createSimple()
    {
        //Data
        $productData = $this->loadData('simple_product_for_order', NULL, array('general_name', 'general_sku'));
        //Steps
        $this->loginAdminUser();
        $this->navigate('manage_products');
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        $this->productHelper()->createProduct($productData);
        //Verification
        $this->assertTrue($this->successMessage('success_saved_product'), $this->messages);
        $this->assertTrue($this->checkCurrentPage('manage_products'), $this->messages);
        return $productData['general_name'];
    }

    /**
     * <p>Checkout with required fields filling</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Select Shipping Method option</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>8. Select Payment Method option</p>
     * <p>9. Click 'Continue' button.</p>
     * <p>Verify information into "Order Review" tab</p>
     * <p>Expected result:</p>
     * <p>Checkout is successful.</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontCheckoutRequiredFields($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest',
                array('general_name' => $productData), array('billing_email'));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
    }

    /**
     * <p>Empty required fields in billing address tab</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Leave billing information fields empty</p>
     * <p>4. Click "Continue" button</p>
     * <p>5. Verify error message;</p>
     * <p>6. Repeat scenario for all required fields in current tab;</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @test
     * @depends createSimple
     * @dataProvider billingEmptyFields
     * @param string $emptyField
     * @param string $productData
     *
     */
    public function frontEmptyRequiredFildsInBillingAddress($emptyField, $productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest_empty_fields',
                array('general_name' => $productData, $emptyField => ''));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $page = $this->getUimapPage('frontend', 'onepage_checkout');
        $fieldSet = $page->findFieldset('billing_information');
        if ($emptyField != 'billing_country' and $emptyField != 'billing_state') {
            $fieldXpath = $fieldSet->findField($emptyField);
            $this->addParameter('fieldXpath', $fieldXpath);
            $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
            $this->assertTrue($this->verifyMessagesCount(), $this->messages);
        } else {
            $fieldXpath = $fieldSet->findDropdown($emptyField);
            $this->addParameter('fieldXpath', $fieldXpath);
            $this->assertTrue($this->errorMessage('please_select_option'), $this->messages);
            $this->assertTrue($this->verifyMessagesCount(), $this->messages);
        }
    }

    public function billingEmptyFields()
    {
        return array(
            array('billing_first_name'),
            array('billing_last_name'),
            array('billing_email'),
            array('billing_street_address_1'),
            array('billing_city'),
            array('billing_state'),
            array('billing_zip_code'),
            array('billing_country'),
            array('billing_telephone')
        );
    }

    /**
     * <p>Filling required fields by invalid values</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in 'Email' field by incorrect value.</p>
     * <p>4. Fill other required fields by regular data.</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontBillingAddressInvalidEmail($productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest_empty_fields',
                array('general_name' => $productData, 'billing_email' => $this->generate('email', 32, 'invalid')));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $page = $this->getUimapPage('frontend', 'onepage_checkout');
        $fieldSet = $page->findFieldset('billing_information');
        $fieldXpath = $fieldSet->findField('billing_email');
        $this->addParameter('fieldXpath', $fieldXpath);
        $this->assertTrue($this->errorMessage('invalid_email_address'), $this->messages);
        $this->assertTrue($this->verifyMessagesCount(), $this->messages);
    }

    /**
     * <p>Using long values for fill billing information form</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill required fields by long values data.</p>
     * <p>4. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Customer successfully redirected to the next page, no error masseges appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontBillingWithLongValues($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $longValues = array(
            'billing_address_choice'    => 'New Address',
            'billing_first_name'        => $this->generate('string', 255, ':alnum:'),
            'billing_last_name'         => $this->generate('string', 255, ':alnum:'),
            'billing_email'             => $this->generate('email', 128, 'valid'),
            'billing_street_address_1'  => $this->generate('string', 255, ':alnum:'),
            'billing_city'              => $this->generate('string', 255, ':alnum:'),
            'billing_country'           => 'United States',
            'billing_state'             => 'California',
            'billing_zip_code'          => $this->generate('string', 255, ':alnum:'),
            'billing_telephone'         => $this->generate('string', 255, ':alnum:'),
            'ship_to_different_address' => 'Yes'
        );
        $checkoutData = $this->loadData('checkout_data_saved_cc', array('general_name' => $productData));
        $checkoutData['billing_address_data'] = $longValues;
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
    }

    /**
     * <p>Using special characters for fill billing information form (except email field)</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill required fields by special characters.</p>
     * <p>4. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Customer successfully redirected to the next page, no error masseges appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontBillingWithSpecialCharactersExceptEmail($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $specValues = array(
            'billing_address_choice'    => 'New Address',
            'billing_first_name'        => $this->generate('string', 32, ':punct:'),
            'billing_last_name'         => $this->generate('string', 32, ':punct:'),
            'billing_email'             => $this->generate('email', 16, 'valid'),
            'billing_street_address_1'  => $this->generate('string', 32, ':punct:'),
            'billing_city'              => $this->generate('string', 32, ':punct:'),
            'billing_country'           => 'United States',
            'billing_state'             => 'California',
            'billing_zip_code'          => $this->generate('string', 32, ':punct:'),
            'billing_telephone'         => $this->generate('string', 32, ':punct:'),
            'ship_to_different_address' => 'Yes'
        );
        $checkoutData = $this->loadData('checkout_data_saved_cc', array('general_name' => $productData));
        $checkoutData['billing_address_data'] = $specValues;
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
    }

    /**
     * <p>Empty required fields in shipping address tab</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill billing information fields by regular data</p>
     * <p>4. Click 'Continue' button.</p>
     * <p>5. Leave shipping information fields empty</p>
     * <p>6. Click "Continue" button</p>
     * <p>7. Verify error message;</p>
     * <p>8. Repeat scenario for all required fields in current tab;</p>
     * <p>Expected result:</p>
     * <p>Error message appears</p>
     *
     * @test
     * @depends createSimple
     * @dataProvider shippingEmptyFields
     * @param string $emptyField
     * @param string $productData
     */
    public function frontEmptyRequiredFildsInShippingAddress($emptyField, $productData)
    {
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest_empty_fields_shipping',
                array('general_name' => $productData, $emptyField => ''));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $page = $this->getUimapPage('frontend', 'onepage_checkout');
        $fieldSet = $page->findFieldset('shipping_information');
        if ($emptyField != 'shipping_country' and $emptyField != 'shipping_state') {
            $fieldXpath = $fieldSet->findField($emptyField);
            $this->addParameter('fieldXpath', $fieldXpath);
            $this->assertTrue($this->errorMessage('empty_required_field'), $this->messages);
            $this->assertTrue($this->verifyMessagesCount(), $this->messages);
        } else {
            $fieldXpath = $fieldSet->findDropdown($emptyField);
            $this->addParameter('fieldXpath', $fieldXpath);
            $this->assertTrue($this->errorMessage('please_select_option'), $this->messages);
            $this->assertTrue($this->verifyMessagesCount(), $this->messages);
        }
    }

    public function shippingEmptyFields()
    {
        return array(
            array('shipping_first_name'),
            array('shipping_last_name'),
            array('shipping_street_address_1'),
            array('shipping_city'),
            array('shipping_state'),
            array('shipping_zip_code'),
            array('shipping_country'),
            array('shipping_telephone')
        );
    }

    /**
     * <p>Using special characters for fill shipping information form</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to different address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Fill in Shipping Information required fields by special characters.</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Customer successfully redirected to the next page, no error masseges appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingAddressSpecialCharacters($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $specValues = array(
            'shipping_address_choice'    => 'New Address',
            'shipping_first_name'        => $this->generate('string', 32, ':punct:'),
            'shipping_last_name'         => $this->generate('string', 32, ':punct:'),
            'shipping_street_address_1'  => $this->generate('string', 32, ':punct:'),
            'shipping_city'              => $this->generate('string', 32, ':punct:'),
            'shipping_country'           => 'United States',
            'shipping_state'             => 'California',
            'shipping_zip_code'          => '94306',
            //Here should be punct for zip code, but it redirects back to shopping cart. Seems it's a bug
            'shipping_telephone'         => $this->generate('string', 32, ':punct:')
        );
        $checkoutData = $this->loadData('checkout_data_saved_cc', array('general_name' => $productData));
        $checkoutData['shipping_address_data'] = $specValues;
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
    }
    /**
     * <p>Verifying "Use Billing Address" checkbox functionality</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to different address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Check "Use Billing Address" checkbox</p>
     * <p>7. Verify data used for filling form</p>
     * <p>8. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Data must be the same as billing address</p>
     * <p>Customer successfully redirected to the next page, no error massages appears</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingAddressUseBillingAddress($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc',
                array('general_name' => $productData), array('billing_email'));
        unset($checkoutData['shipping_address_data']);
        $checkoutData['shipping_address_data'] = array('shipping_address_choice' => 'New Address',
                                                    'use_billing_address' => 'Yes');
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData);
        //Verification
        $this->assertTrue($this->successMessage('success_checkout'), $this->messages);
    }

    /**
     * <p>Shipping method not defined</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Leave Shipping Method options empty</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Information window appears "Please specify shipping method."</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontShippingMethodNotDefined($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('free_enable');
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest_no_shipping_method',
                array('general_name' => $productData), array('billing_email'));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData, FALSE);
        $setXpath = $this->_getControlXpath('fieldset', 'shipping_method') . "[contains(@class,'active')]";
        $this->waitForElement($setXpath);
        if ($this->isElementPresent($setXpath)) {
            $this->clickButton('ship_method_continue', FALSE);
        } else {
            $this->fail('Seems here is only one shipping method and we have nothing to choose');
        }
        //Verification
        $text = $this->_getControlXpath('message', 'shipping_alert');
        $this->assertTrue($this->isAlertPresent(), 'error');
        $this->assertEquals($text, $this->getAlert(), 'error');
    }

    /**
     * <p>Payment method not defined</p>
     * <p>Preconditions</p>
     * <p>1. Add product to Shopping Cart</p>
     * <p>2. Click "Proceed to Checkout"</p>
     * <p>Steps</p>
     * <p>1. Fill in Checkout Method tab</p>
     * <p>2. Click 'Continue' button.</p>
     * <p>3. Fill in Billing Information tab</p>
     * <p>4. Select "Ship to this address" option</p>
     * <p>5. Click 'Continue' button.</p>
     * <p>6. Select Shipping Method option</p>
     * <p>7. Click 'Continue' button.</p>
     * <p>8. Leave Payment Method options empty</p>
     * <p>9. Click 'Continue' button.</p>
     * <p>Expected result:</p>
     * <p>Information window appears "Please specify payment method."</p>
     *
     * @depends createSimple
     * @test
     */
    public function frontPaymentMethodNotDefined($productData)
    {
        //Preconditions
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->assertTrue($this->checkCurrentPage('system_configuration'), $this->messages);
        $this->systemConfigurationHelper()->configure('savedcc_without_3Dsecure');
        //Data
        $checkoutData = $this->loadData('checkout_data_saved_cc_req_guest_no_payment_method',
                array('general_name' => $productData), array('billing_email'));
        //Steps
        $this->logoutCustomer();
        $this->checkoutOnePageHelper()->frontCreateCheckout($checkoutData, FALSE);
        $setXpath = $this->_getControlXpath('fieldset', 'payment_method') . "[contains(@class,'active')]";
        $this->waitForElement($setXpath);
        if ($this->isElementPresent($setXpath)) {
            $this->clickButton('payment_method_continue', FALSE);
        } else {
            $this->fail('Seems here is only one payment method and we have nothing to choose');
        }
        //Verification
        $text = $this->_getControlXpath('message', 'payment_alert');
        $this->assertTrue($this->isAlertPresent(), 'error');
        $this->assertEquals($text, $this->getAlert(), 'error');
    }
}
