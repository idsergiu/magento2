<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Adminhtml order tax totals block
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Sales_Order_Totals_Tax extends Mage_Tax_Block_Sales_Order_Tax
{
    /**
     * Get full information about taxes applied to order
     *
     * @return array
     */
    public function getFullTaxInfo()
    {
        /** @var $source Mage_Sales_Model_Order */
        $source = $this->getOrder();

        $taxClassAmount = array();
        if ($source instanceof Mage_Sales_Model_Order) {
            $taxClassAmount = Mage::helper('Mage_Tax_Helper_Data')->getCalculatedTaxes($source);
            if (empty($taxClassAmount)) {
                $rates = Mage::getModel('Mage_Sales_Model_Order_Tax')->getCollection()->loadByOrder($source)->toArray();
                $taxClassAmount =  Mage::getSingleton('Mage_Tax_Model_Calculation')->reproduceProcess($rates['items']);
            } else {
                $shippingTax    = Mage::helper('Mage_Tax_Helper_Data')->getShippingTax($source);
                $taxClassAmount = array_merge($shippingTax, $taxClassAmount);
            }
        }

        return $taxClassAmount;
    }

    /**
     * Display tax amount
     *
     * @return string
     */
    public function displayAmount($amount, $baseAmount)
    {
        return Mage::helper('Mage_Adminhtml_Helper_Sales')->displayPrices(
            $this->getSource(), $baseAmount, $amount, false, '<br />'
        );
    }

    /**
     * Get store object for process configuration settings
     *
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore();
    }
}
