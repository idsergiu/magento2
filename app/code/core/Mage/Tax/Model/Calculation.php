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
 * @category   Mage
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Calculation Model
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Calculation extends Mage_Core_Model_Abstract
{
    const CALC_PRICE_EXCL_TAX_DISCOUNT_ON_EXCL      = '0_0_0';
    const CALC_PRICE_EXCL_TAX_DISCOUNT_ON_INCL      = '0_0_1';
    const CALC_PRICE_EXCL_TAX_AFTER_DISCOUNT_ON_EXCL= '0_1_1';
    const CALC_PRICE_INCL_TAX_DISCOUNT_ON_EXCL      = '1_0_0';
    const CALC_PRICE_INCL_TAX_DISCOUNT_ON_INCL      = '1_0_1';
    const CALC_PRICE_INCL_TAX_AFTER_DISCOUNT_ON_INCL= '1_1_1';

    const CALC_UNIT_BASE    = 'UNIT_BASE_CALCULATION';
    const CALC_ROW_BASE     = 'ROW_BASE_CALCULATION';
    const CALC_TOTAL_BASE   = 'TOTAL_BASE_CALCULATION';

    protected $_rates   = array();
    protected $_ctc     = array();
    protected $_ptc     = array();

    protected $_rateCache = array();
    protected $_rateCalculationProcess = array();

    protected function _construct()
    {
        $this->_init('tax/calculation');
    }

    /**
     * Delete calculation settings by rule id
     *
     * @param   int $ruleId
     * @return  Mage_Tax_Model_Calculation
     */
    public function deleteByRuleId($ruleId)
    {
        $this->_getResource()->deleteByRuleId($ruleId);
        return $this;
    }

    /**
     * Get calculation rates by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getRates($ruleId)
    {
        if (!isset($this->_rates[$ruleId])) {
            $this->_rates[$ruleId] = $this->_getResource()->getDistinct('tax_calculation_rate_id', $ruleId);
        }
        return $this->_rates[$ruleId];
    }

    /**
     * Get allowed customer tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getCustomerTaxClasses($ruleId)
    {
        if (!isset($this->_ctc[$ruleId])) {
            $this->_ctc[$ruleId] = $this->_getResource()->getDistinct('customer_tax_class_id', $ruleId);
        }
        return $this->_ctc[$ruleId];
    }

    /**
     * Get allowed product tax classes by rule id
     *
     * @param   int $ruleId
     * @return  array
     */
    public function getProductTaxClasses($ruleId)
    {
        if (!isset($this->_ptc[$ruleId])) {
            $this->_ptc[$ruleId] = $this->getResource()->getDistinct('product_tax_class_id', $ruleId);
        }
        return $this->_ptc[$ruleId];
    }

    protected function _formCalculationProcess()
    {
        $title = $this->getRateTitle();
        $value = $this->getRateValue();
        $id = $this->getRateId();

        $rate = array('code'=>$title, 'title'=>$title, 'percent'=>$value, 'position'=>1, 'priority'=>1);

        $process = array();
        $process['percent'] = $value;
        $process['id'] = "{$id}-{$value}";
        $process['rates'][] = $rate;

        return array($process);
    }

    /**
     * Get calculation tax rate by specific request
     * 
     * @param   Varien_Object $request
     * @return  float
     */
    public function getRate($request)
    {
        if (!$request->getCountryId() || !$request->getCustomerClassId() || !$request->getProductClassId()) {
            return 0;
        }

        $cacheKey = $request->getProductClassId() . '|' . $request->getCustomerClassId() . '|'
            . $request->getCountryId() . '|' . $request->getRegionId() . '|' . $request->getPostcode();

        if (!isset($this->_rateCache[$cacheKey])) {
            $this->unsRateValue();
            $this->unsCalculationProcess();
            $this->unsEventModuleId();
            Mage::dispatchEvent('tax_rate_data_fetch', array('request'=>$this));
            if (!$this->hasRateValue()) {
                $this->setCalculationProcess($this->_getResource()->getCalculationProcess($request));
                $this->setRateValue($this->_getResource()->getRate($request));
            } else {
                $this->setCalculationProcess($this->_formCalculationProcess());
            }
            $this->_rateCache[$cacheKey] = $this->getRateValue();
            $this->_rateCalculationProcess[$cacheKey] = $this->getCalculationProcess();
        }
        return $this->_rateCache[$cacheKey];
    }

    /**
     * Get request object for getting tax rate
     *
     * @param   null|Varien_Object $shippingAddress
     * @param   null|Varien_Object $billingAddress
     * @param   null|int $customerTaxClass
     * @param   null|int $store
     * @return  Varien_Object
     */
    public function getRateRequest($shippingAddress = null, $billingAddress = null, $customerTaxClass = null, $store = null)
    {
        $address = new Varien_Object();
        $session = Mage::getSingleton('customer/session');
        $basedOn = Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_BASED_ON, $store);
        if (($shippingAddress === false && $basedOn == 'shipping') || ($billingAddress === false && $basedOn == 'billing')) {
            $basedOn = 'default';
        } else {
            if ((($billingAddress === false || is_null($billingAddress) || !$billingAddress->getCountryId()) && $basedOn == 'billing') || (($shippingAddress === false || is_null($shippingAddress) || !$shippingAddress->getCountryId()) && $basedOn == 'shipping')){
                if (!$session->isLoggedIn()) {
                    $basedOn = 'default';
                } else {
                    $defBilling = $session->getCustomer()->getDefaultBillingAddress();
                    $defShipping = $session->getCustomer()->getDefaultShippingAddress();

                    if ($basedOn == 'billing' && $defBilling && $defBilling->getCountryId()) {
                        $billingAddress = $defBilling;
                    } else if ($basedOn == 'shipping' && $defShipping && $defShipping->getCountryId()) {
                        $shippingAddress = $defShipping;
                    } else {
                        $basedOn = 'default';
                    }
                }
            }
        }

        switch ($basedOn) {
            case 'billing':
                $address = $billingAddress;
                break;

            case 'shipping':
                $address = $shippingAddress;
                break;

            case 'origin':
                $address
                    ->setCountryId(Mage::getStoreConfig('shipping/origin/country_id', $store))
                    ->setRegionId(Mage::getStoreConfig('shipping/origin/region_id', $store))
                    ->setPostcode(Mage::getStoreConfig('shipping/origin/postcode', $store));
                break;

            case 'default':
                $address
                    ->setCountryId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY, $store))
                    ->setRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION, $store))
                    ->setPostcode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE, $store));
                break;
        }

        if (is_null($customerTaxClass) && $session->isLoggedIn()) {
            $customerTaxClass = $session->getCustomer()->getTaxClassId();
        } elseif (($customerTaxClass === false) || !$session->isLoggedIn()) {
            $defaultCustomerGroup = Mage::getStoreConfig('customer/create_account/default_group', $store);
            $customerTaxClass = Mage::getModel('customer/group')->getTaxClassId($defaultCustomerGroup);
        }
        $request = new Varien_Object();
        $request
            ->setCountryId($address->getCountryId())
            ->setRegionId($address->getRegionId())
            ->setPostcode($address->getPostcode())
            ->setStore($store)
            ->setCustomerClassId($customerTaxClass);
        return $request;
    }

    protected function _getRates($request, $fieldName, $type)
    {
        $result = array();
        $classes = Mage::getModel('tax/class')->getCollection()
            ->addFieldToFilter('class_type', $type)
            ->load();
        foreach ($classes as $class) {
            $request->setData($fieldName, $class->getId());
            $result[$class->getId()] = $this->getRate($request);
        }

        return $result;
    }

    public function getRatesForAllProductTaxClasses($request)
    {
        return $this->_getRates($request, 'product_class_id', 'PRODUCT');
    }
    public function getRatesForAllCustomerTaxClasses($request)
    {
        return $this->_getRates($request, 'customer_class_id', 'CUSTOMER');
    }

    /**
     * Get information about tax rates applied to request
     *
     * @param   Varien_Object $request
     * @return  array
     */
    public function getAppliedRates($request)
    {
        $cacheKey = $request->getStore()->getId() . '|' . $request->getProductClassId() . '|'
            . $request->getCustomerClassId() . '|' . $request->getCountryId() . '|'
            . $request->getRegionId() . '|' . $request->getPostcode();

        if (!isset($this->_rateCalculationProcess[$cacheKey])) {
            $this->_rateCalculationProcess[$cacheKey] = $this->_getResource()->getCalculationProcess($request);
        }
        return $this->_rateCalculationProcess[$cacheKey];
    }

    public function reproduceProcess($rates)
    {
        return $this->getResource()->getCalculationProcess(null, $rates);
    }

    public function getRatesByCustomerTaxClass($customerTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass);
    }

    public function getRatesByCustomerAndProductTaxClasses($customerTaxClass, $productTaxClass)
    {
        return $this->getResource()->getRatesByCustomerTaxClass($customerTaxClass, $productTaxClass);
    }

    /**
     * Calculate rated tax abount based on price and tax rate.
     * If you are using price including tax $priceIncludeTax should be true.
     * $taxRate can't be more than 1 (if it is not percent)
     *
     * @param   float $price
     * @param   float $taxRate 
     * @param   boolean $priceIncludeTax
     * @return  float
     */
    public function calcTaxAmount($price, $taxRate, $priceIncludeTax=false, $round=true)
    {
        /**
         * $taxRate can be more than 1 if somebody use tax percent
         */
        if ($taxRate>1) {
            $taxRate = $taxRate/100;
        }

        if ($priceIncludeTax) {
            $amount = $price*(1-1/(1+$taxRate));
        } else {
            $amount = $price*$taxRate;
        }

        if ($round) {
            return $this->round($amount);
        } else {
            return $amount;
        }
    }

    /**
     * Truncate number to specified precision
     *
     * @param   float $price
     * @param   int $precision
     * @return  float
     */
    public function truncate($price, $precision=4)
    {
        $exp = pow(10,$precision);
        $price = floor($price*$exp)/$exp;
        return $price;
    }

    /**
     * Round tax amount
     *
     * @param   float $price
     * @return  float
     */
    public function round($price)
    {
        return Mage::app()->getStore()->roundPrice($price);
    }
}