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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 *
 * PayPal Express Module
 */
class Mage_Paypal_Model_Express extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;
    protected $_formBlockType = 'paypal/express_form';
    protected $_infoBlockType = 'paypal/payment_info';

    /**
     * Website Payments Pro instance type
     *
     * @var $_proType string
     */
    protected $_proType = 'paypal/pro';

    /**
     * Availability options
     */
    protected $_isGateway               = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = true;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canFetchTransactionInfo = true;

    /**
     * Ipn action
     *
     * @var string
     */
    protected $_ipnAction = 'paypal/ipn/express';

    /**
     * Website Payments Pro instance
     *
     * @var Mage_Paypal_Model_Pro
     */
    protected $_pro = null;

    public function __construct($params = array())
    {
        $proInstance = array_shift($params);
        if ($proInstance && ($proInstance instanceof Mage_Paypal_Model_Pro)) {
            $this->_pro = $proInstance;
        } else {
            $this->_pro = Mage::getModel($this->_proType);
        }
        $this->_pro->setMethod($this->_code);
    }

    /**
     * Check whether payment method is available in checkout
     * Return false if PayFlow edition enabled
     *
     * TODO?
     * Also check obligatory data such as Credentials API or Merchant email
     *
     * @param Mage_Sales_Model_Quote $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (!parent::isAvailable($quote)) {
            return false;
        }
        if ($this->_pro->getConfig()->usePayflow) {
            return false;
        }
        return true;
    }

    /**
     * Store setter
     * Also updates store ID in config object
     *
     * @param Mage_Core_Model_Store|int $store
     */
    public function setStore($store)
    {
        $this->setData('store', $store);
        $this->_pro->getConfig()->setStoreId(is_object($store) ? $store->getId() : $store);
        return $this;
    }

    /**
     * Whether method is available for specified currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        return $this->_pro->getConfig()->isCurrencyCodeSupported($currencyCode);
    }

    /**
     * Payment action getter compatible with payment model
     *
     * @see Mage_Sales_Model_Payment::place()
     * @return string
     */
    public function getConfigPaymentAction()
    {
        return $this->_pro->getConfig()->getPaymentAction();
    }

    /**
     * Custom getter for payment configuration
     *
     * @param string $field
     * @param int $storeId
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStore();
        }
        switch ($field)
        {
            case 'allowspecific':
            case 'specificcountry':
            case 'line_items_enabled':
            case 'business_account':
                $path = 'paypal/general/' . $field;
            default:
                $path = 'payment/' . Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS . '/' . $field;
        }
        return Mage::getStoreConfig($path, $storeId);
    }

    /**
     * Authorize payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        return $this->_placeOrder($payment, $amount);
    }

    /**
     * Void payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function void(Varien_Object $payment)
    {
        $this->_pro->void($payment);
        return $this;
    }

    /**
     * Capture payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function capture(Varien_Object $payment, $amount)
    {
        if (false === $this->_pro->capture($payment, $amount)) {
            $this->_placeOrder($payment, $amount);
        }
        return $this;
    }

    /**
     * Refund capture
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $this->_pro->refund($payment, $amount);
        return $this;
    }

    /**
     * Cancel payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function cancel(Varien_Object $payment)
    {
        $this->_pro->cancel($payment);
        return $this;
    }

    /**
     * Accept payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function accept(Varien_Object $payment)
    {
        $this->_pro->accept($payment);
        return $this;
    }

    /**
     * Deny payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Mage_Paypal_Model_Express
     */
    public function deny(Varien_Object $payment)
    {
        $this->_pro->deny($payment);
        return $this;
    }

    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
        return Mage::getUrl('paypal/express/start');
    }

    /**
     * Fetch transaction details info
     *
     * @param string $transactionId
     * @return array
     */
    public function fetchTransactionInfo($transactionId)
    {
        return $this->_pro->fetchTransactionInfo($transactionId);
    }

    /**
     * Place an order with authorization or capture action
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float $amount
     * @return Mage_Paypal_Model_Express
     */
    protected function _placeOrder(Mage_Sales_Model_Order_Payment $payment, $amount)
    {
        // prepare api call
        $order = $payment->getOrder();
        $token = $payment->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_TOKEN);
        $api = $this->_pro->getApi()
            ->setToken($token)
            ->setPayerId($payment->getAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_PAYER_ID))
            ->setAmount($amount)
            ->setPaymentAction($this->_pro->getConfig()->paymentAction)
            ->setNotifyUrl(Mage::getUrl($this->_ipnAction))
            ->setInvNum($order->getIncrementId())
            ->setCurrencyCode($order->getBaseCurrencyCode())
            ->setBusinessAccount($this->_pro->getConfig()->businessAccount);
        ;

        // add line items
        if ($this->_pro->getConfig()->lineItemsEnabled) {
            list($items, $totals) = Mage::helper('paypal')->prepareLineItems($order);
            if (Mage::helper('paypal')->areCartLineItemsValid($items, $totals, $amount)) {
                $api->setLineItems($items)->setLineItemTotals($totals);
            }
        }

        // call api and get details from it
        $api->callDoExpressCheckoutPayment();
        $this->_importToPayment($api, $payment);
        return $this;
    }

    /**
     * Import payment info to payment
     *
     * @param Mage_Paypal_Model_Api_Nvp
     * @param Mage_Sales_Model_Order_Payment
     */
    protected function _importToPayment($api, $payment)
    {
        $payment->setTransactionId($api->getTransactionId())->setIsTransactionClosed(0)
            ->setAdditionalInformation(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_REDIRECT,
                $api->getRedirectRequired() || $api->getRedirectRequested()
            )
            ->setIsTransactionPending($api->getIsPaymentPending());
        Mage::getModel('paypal/info')->importToPayment($api, $payment);
    }
}
