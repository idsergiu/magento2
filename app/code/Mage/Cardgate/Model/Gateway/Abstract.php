<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Cardgate
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @category   Mage
 * @package    Mage_Cardgate
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Cardgate_Model_Gateway_Abstract extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Checkout Session
     *
     * @var Mage_Checkout_Model_Session
     */
    protected $_checkoutSession;

    /**
     * Sales Order factory
     *
     * @var Mage_Sales_Model_OrderFactory
     */
    protected $_orderFactory;

    /**
     * URL generator
     *
     * @var Mage_Core_Model_Url
     */
    protected $_urlGenerator;

    /**
     * Card Gate Base Object
     *
     * @var Mage_Cardgate_Model_Base
     */
    protected $_base;

    /**
     * Store Config object
     *
     * @var Mage_Core_Model_Store_Config
     */
    protected $_storeConfig;

    /**
     * Helper object
     *
     * @var Mage_Cardgate_Helper_Data
     */
    protected $_helper;

    /**
     * Cardgate Form Block class name
     *
     * @var string
     */
    protected $_formBlockType = 'Mage_Cardgate_Block_Form';

    /**
     * Cardgate Payment Method Code
     *
     * @var string
     */
    protected $_code;

    /**
     * Cardgate Payment Model Code
     *
     * @var string
     */
    protected $_model;

    /**
     * CardGatePlus features
     *
     * @var mixed
     */
    protected $_url = 'https://gateway.cardgateplus.com/';

    protected $_supportedCurrencies = array(
        'EUR', 'USD', 'JPY', 'BGN', 'CZK',
        'DKK', 'GBP', 'HUF', 'LTL', 'LVL',
        'PLN', 'RON', 'SEK', 'CHF', 'NOK',
        'HRK', 'RUB', 'TRY', 'AUD', 'BRL',
        'CAD', 'CNY', 'HKD', 'IDR', 'ILS',
        'INR', 'KRW', 'MXN', 'MYR', 'NZD',
        'PHP', 'SGD', 'THB', 'ZAR',
    );

    /**
     * Mage_Payment_Model settings
     *
     * @var bool
     */
    protected $_isGateway               = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;

    public function __construct(
        Mage_Checkout_Model_Session $checkoutSession,
        Mage_Sales_Model_OrderFactory $orderFactory,
        Mage_Core_Model_Url $urlGenerator,
        Mage_Core_Model_Store_Config $storeConfig,
        Mage_Cardgate_Model_Base $base,
        Mage_Cardgate_Helper_Data $helper
    ) {
        parent::__construct();

        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_urlGenerator = $urlGenerator;
        $this->_storeConfig = $storeConfig;
        $this->_base = $base;
        $this->_helper = $helper;
    }

    /**
     * Return Gateway Url
     *
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->_url;
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get current order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->_orderFactory->create();
        $order->loadByIncrementId($this->_checkoutSession->getLastRealOrderId());
        return $order;
    }

    /**
     * Magento tries to set the order from payment/, instead of cardgate/
     *
     * @param Mage_Sales_Model_Order $order
     * @return void
     */
    public function setSortOrder($order)
    {
        $this->sort_order = $this->getConfigData('sort_order');
    }

    /**
     * Append the current model to the URL
     *
     * @param string $url
     * @return string
     */
    function getModelUrl($url)
    {
        $params = array(
            '_secure' => true
        );
        if (!empty($this->_model)) {
            $params['model'] = $this->_model;
        }
        return $this->_urlGenerator->getUrl($url, $params);
    }

    /**
     * Magento will use this for payment redirection
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return $this->getModelUrl('cardgate/cardgate/redirect');
    }

    /**
     * Retrieve config value for store by path
     *
     * @param $field
     * @param null $storeId
     * @internal param string $path
     * @internal param mixed $store
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        $value = parent::getConfigData($field, $storeId);

        if ($field != 'active' && !$value) {
            if (null === $storeId) {
                $storeId = $this->getStore();
            }
            $path = 'payment/cardgate/' . $field;
            $value = $this->_storeConfig->getConfig($path, $storeId);
        }

        return $value;
    }

    /**
     * Validate if the currency code is supported by CardGatePlus
     *
     * @return Mage_Cardgate_Model_Gateway_Abstract
     */
    public function validate()
    {
        parent::validate();

        $currency_code = $this->getQuote()->getBaseCurrencyCode();
        if (!in_array($currency_code, $this->_supportedCurrencies)) {
            $this->_base->log('Unacceptable currency code (' . $currency_code . ').');
            Mage::throwException(
                $this->_helper->__('Selected currency code ') . $currency_code .
                    $this->_helper->__(' is not compatible with CardGatePlus'));
        }

        return $this;
    }

    /**
     * Generates checkout form fields
     *
     * @return array
     */
    public function getCheckoutFormFields()
    {
        $order = $this->getOrder();
        $customer = $order->getBillingAddress();
        // Change order status
        $newState = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
        $newStatus = $this->getConfigData('initialized_status');
        $statusMessage = $this->_helper->__('Transaction started, waiting for payment.');
        $order->setState($newState, $newStatus, $statusMessage);
        $order->save();
        $s_arr = array();
        switch ($this->_model) {
            // Credit cards
            case 'creditcard':
                $s_arr['option'] = 'creditcard';
                break;

            // DIRECTebanking
            case 'sofortbanking':
                $s_arr['option'] = 'directebanking';
                break;

            // iDEAL
            case 'ideal':
                $s_arr['option'] = 'ideal';
                $s_arr['suboption'] = $order->getPayment()->getAdditionalInformation('ideal_issuer_id');
                break;

            // Mister Cash
            case 'mistercash':
                $s_arr['option'] = 'mistercash';
                break;

            // Default
            default:
                $s_arr['option'] = '';
                $s_arr['suboption'] = '';
                break;
        }

        $currency_code = $order->getBaseCurrencyCode();

        $orderId = $order->getIncrementId();
        $orderId = $this->getConfigData('transaction_id_prefix')
            ? $this->getConfigData('transaction_id_prefix') . '-' . $orderId
            : $orderId;

        $s_arr['siteid'] = $this->getConfigData('site_id');
        $s_arr['ref'] = $orderId;
        $s_arr['first_name'] = $customer->getFirstname();
        $s_arr['last_name'] = $customer->getLastname();
        $s_arr['email'] = $order->getCustomerEmail();
        $s_arr['address'] = $customer->getStreet(1) .
            ($customer->getStreet(2) ? ', ' . $customer->getStreet(2) : '');
        $s_arr['city'] = $customer->getCity();
        $s_arr['country_code'] = $customer->getCountry();
        $s_arr['postal_code'] = $customer->getPostcode();
        $s_arr['phone_number'] = $customer->getTelephone();
        $s_arr['state'] = $customer->getRegionCode();
        $s_arr['language']  = $this->getConfigData('lang');
        $s_arr['return_url'] = $this->_urlGenerator->getUrl('cardgate/cardgate/success/', array('_secure' => true));
        $s_arr['return_url_failed'] =
            $this->_urlGenerator->getUrl('cardgate/cardgate/cancel/', array('_secure' => true));
        $s_arr['shop_version'] = 'Magento ' . Mage::getVersion();
//        $s_arr['plugin_name'] = 'Cardgate_Cgp';
//        $s_arr['plugin_version'] = '1.0.1';

        if ($this->_base->isTest()) {
            $s_arr['test'] = '1';
            $hash_prefix = 'TEST';
        } else {
            $hash_prefix = '';
        }

        $s_arr['currency'] = $currency_code;
        $s_arr['amount'] = sprintf('%.0f', $order->getBaseTotalDue() * 100);
        $s_arr['description'] = str_replace('%id%',
            $orderId,
            $this->getConfigData('order_description'));
        $s_arr['hash'] = md5($hash_prefix .
            $this->getConfigData('site_id') .
            $s_arr['amount'] .
            $s_arr['ref'] .
            $this->getConfigData('hash_key'));

        // Logging
        $this->_base->log('Initiating a new transaction');
        $this->_base->log('Sending customer to CardGatePlus with values:');
        $this->_base->log('URL = ' . $this->getGatewayUrl());
        $this->_base->log($s_arr);

        return $s_arr;
    }
}
