<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Paypal Data helper
 */
class Mage_Paypal_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Cache for shouldAskToCreateBillingAgreement()
     *
     * @var bool
     */
    protected static $_shouldAskToCreateBillingAgreement = null;

    /**
     * Check whether customer should be asked confirmation whether to sign a billing agreement
     *
     * @param Mage_Paypal_Model_Config $config
     * @param int $customerId
     * @return bool
     */
    public function shouldAskToCreateBillingAgreement(Mage_Paypal_Model_Config $config, $customerId)
    {
        if (null === self::$_shouldAskToCreateBillingAgreement) {
            self::$_shouldAskToCreateBillingAgreement = false;
            if ($customerId && $config->shouldAskToCreateBillingAgreement()) {
                if (Mage::getModel('Mage_Sales_Model_Billing_Agreement')->needToCreateForCustomer($customerId)) {
                    self::$_shouldAskToCreateBillingAgreement = true;
                }
            }
        }
        return self::$_shouldAskToCreateBillingAgreement;
    }

    /**
     * Return backend config for element like JSON
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function getElementBackendConfig(Varien_Data_Form_Element_Abstract $element) {
        $config = $element->getFieldConfig()->backend_congif;
        if (!$config) {
            return false;
        }
        $config = $config->asCanonicalArray();
        if (isset($config['enable_for_countries'])) {
            $config['enable_for_countries'] = explode(',', str_replace(' ', '', $config['enable_for_countries']));
        }
        if (isset($config['disable_for_countries'])) {
            $config['disable_for_countries'] = explode(',', str_replace(' ', '', $config['disable_for_countries']));
        }
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($config);
    }
}
