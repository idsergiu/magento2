<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Directory currency abstract backend model
 *
 * Allows dispatching before and after events for each controller action
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Backend_Model_Config_Backend_Currency_Abstract extends Mage_Core_Model_Config_Data
{
    /**
     * Retrieve allowed currencies for current scope
     *
     * @return array
     */
    protected function _getAllowedCurrencies()
    {
        if ($this->getData('groups/options/fields/allow/inherit')) {
            return explode(',', Mage::getConfig()->getNode('currency/options/allow',
                $this->getScope(), $this->getScopeId())
            );
        }
        return $this->getData('groups/options/fields/allow/value');
    }

    /**
     * Retrieve Installed Currencies
     *
     * @return array
     */
    protected function _getInstalledCurrencies()
    {
        return explode(',', Mage::getStoreConfig('system/currency/installed'));
    }

    /**
     * Retrieve Base Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyBase()
    {
        if (!$value = $this->getData('groups/options/fields/base/value')) {
            $value = Mage::getConfig()->getNode(
                Mage_Directory_Model_Currency::XML_PATH_CURRENCY_BASE,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }

    /**
     * Retrieve Default desplay Currency value for current scope
     *
     * @return string
     */
    protected function _getCurrencyDefault()
    {
        if (!$value = $this->getData('groups/options/fields/default/value')) {
            $value = Mage::getConfig()->getNode(
                Mage_Directory_Model_Currency::XML_PATH_CURRENCY_DEFAULT,
                $this->getScope(),
                $this->getScopeId()
            );
        }
        return strval($value);
    }
}
