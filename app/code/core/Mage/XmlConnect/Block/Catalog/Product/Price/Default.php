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
 * @package     Mage_Rss
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Default product price xml renderer
 *
 * @category   Mage
 * @package    Mage_XmlConnect
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_XmlConnect_Block_Catalog_Product_Price_Default extends Mage_Catalog_Block_Product_Price
{
    /**
     * Collect product prices to specified item xml object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Varien_Simplexml_Element $item
     */
    public function collectProductPrices(Mage_Catalog_Model_Product $product, Varien_Simplexml_Element $item)
    {
        $this->setProduct($product)
            ->setDisplayMinimalPrice(true)
            ->setUseLinkForAsLowAs(false);

        $priceXmlObj = $item->addChild('price');

        $_coreHelper = $this->helper('core');
        $_weeeHelper = $this->helper('weee');
        $_taxHelper  = $this->helper('tax');
        /* @var $_coreHelper Mage_Core_Helper_Data */
        /* @var $_weeeHelper Mage_Weee_Helper_Data */
        /* @var $_taxHelper Mage_Tax_Helper_Data */

        $_id = $product->getId();
        $_weeeSeparator = '';
        $_simplePricesTax = ($_taxHelper->displayPriceIncludingTax() || $_taxHelper->displayBothPrices());
        $_minimalPriceValue = $product->getMinimalPrice();
        $_minimalPrice = $_taxHelper->getPrice($product, $_minimalPriceValue, $_simplePricesTax);

        if (!$product->isGrouped()){
            $_weeeTaxAmount = $_weeeHelper->getAmountForDisplay($product);
            if ($_weeeHelper->typeOfDisplay($product, array(1,2,4))){
                $_weeeTaxAmount = $_weeeHelper->getAmount($product);
                $_weeeTaxAttributes = $_weeeHelper->getProductWeeeAttributesForDisplay($product);
            }

            $_price = $_taxHelper->getPrice($product, $product->getPrice());
            $_regularPrice = $_taxHelper->getPrice($product, $product->getPrice(), $_simplePricesTax);
            $_finalPrice = $_taxHelper->getPrice($product, $product->getFinalPrice());
            $_finalPriceInclTax = $_taxHelper->getPrice($product, $product->getFinalPrice(), true);
            $_weeeDisplayType = $_weeeHelper->getPriceDisplayType();
            if ($_finalPrice == $_price){
                if ($_taxHelper->displayBothPrices()){
                    /**
                     * Including
                     */
                    if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 0)){
                        $priceXmlObj->addAttribute('excluding_tax', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                    }
                    /**
                     * Including + Weee
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 1)){
                        $priceXmlObj->addAttribute('excluding_tax', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                        }
                    }
                    /**
                     * Including + Weee
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 4)){
                        $priceXmlObj->addAttribute('excluding_tax', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false));
                        }
                    }
                    /**
                     * Excluding + Weee + Final
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 2)){
                        $priceXmlObj->addAttribute('excluding_tax', $_coreHelper->currency($_price, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                        }
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                    }
                    else {
                         $priceXmlObj->addAttribute('excluding_tax', $_coreHelper->currency($_price, true, false));
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_finalPriceInclTax, true, false));
                    }
                }
                /**
                 * if ($_taxHelper->displayBothPrices()){
                 */
                else {
                    /**
                     * Including
                     */
                     if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 0)){
                         $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                     }
                    /**
                     * Including + Weee
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 1)){
                        $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                        }
                    }
                    /**
                     * Including + Weee
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 4)){
                        $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        $_weeeSeparator = ' + ';
                        $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false));
                        }
                    }
                    /**
                     * Excluding + Weee + Final
                     */
                    elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 2)){
                        $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_price, true, false));
                        $weeeXmlObj = $priceXmlObj->addChild('weee');
                        foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                            $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                            $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                            $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                        }
                        $priceXmlObj->addAttribute('including_tax', $_coreHelper->currency($_price + $_weeeTaxAmount, true, false));
                    }
                    else {
                         $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_price, true, false));
                    }
                }
            }
            /**
             * if ($_finalPrice == $_price){
             */
            else {
                $_originalWeeeTaxAmount = $_weeeHelper->getOriginalAmount($product);
                /**
                 * Including
                 */
                if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 0)){
                    $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false));
                    if ($_taxHelper->displayBothPrices()){
                        $priceXmlObj->addAttribute('special_excluding_tax', $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false));
                        $priceXmlObj->addAttribute('special_including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                    }
                    else {
                         $priceXmlObj->addAttribute('special', $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false));
                    }
                }
                /**
                 * Including + Weee
                 */
                elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 1)){
                    $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false));
                    $priceXmlObj->addAttribute('special_excluding_tax', $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false));
                    $weeeXmlObj = $priceXmlObj->addChild('weee');
                    $_weeeSeparator = ' + ';
                    $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                        $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                        $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                        $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                    }
                    $priceXmlObj->addAttribute('special_including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                }
                /**
                 * Including + Weee
                 */
                elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 4)){
                    $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_regularPrice + $_originalWeeeTaxAmount, true, false));
                    $priceXmlObj->addAttribute('special_excluding_tax', $_coreHelper->currency($_finalPrice + $_weeeTaxAmount, true, false));
                    $weeeXmlObj = $priceXmlObj->addChild('weee');
                    $_weeeSeparator = ' + ';
                    $weeeXmlObj->addAttribute('separator', $_weeeSeparator);
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                        $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                        $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                        $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount() + $_weeeTaxAttribute->getTaxAmount(), true, false));
                    }
                    $priceXmlObj->addAttribute('special_including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                }
                /**
                 * Excluding + Weee + Final
                 */
                elseif ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, 2)){
                    $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_regularPrice, true, false));
                    $priceXmlObj->addAttribute('special_excluding_tax', $_coreHelper->currency($_finalPrice, true, false));
                    $weeeXmlObj = $priceXmlObj->addChild('weee');
                    foreach ($_weeeTaxAttributes as $_weeeTaxAttribute){
                        $weeeItemXmlObj = $weeeXmlObj->addChild('item');
                        $weeeItemXmlObj->addAttribute('name', $weeeItemXmlObj->xmlentities(strip_tags($_weeeTaxAttribute->getName())));
                        $weeeItemXmlObj->addAttribute('amount', $_coreHelper->currency($_weeeTaxAttribute->getAmount(), true, false));
                    }
                    $priceXmlObj->addAttribute('special_including_tax', $_coreHelper->currency($_finalPriceInclTax + $_weeeTaxAmount, true, false));
                }
                /**
                 * Excluding
                 */
                else {
                    $priceXmlObj->addAttribute('regular', $_coreHelper->currency($_regularPrice, true, false));
                    if ($_taxHelper->displayBothPrices()){
                        $priceXmlObj->addAttribute('special_excluding_tax', $_coreHelper->currency($_finalPrice, true, false));
                        $priceXmlObj->addAttribute('special_including_tax', $_coreHelper->currency($_finalPriceInclTax, true, false));
                    }
                    else {
                         $priceXmlObj->addAttribute('special', $_coreHelper->currency($_finalPrice, true, false));
                    }
                }
            }

            if ($this->getDisplayMinimalPrice() && $_minimalPriceValue && $_minimalPriceValue < $product->getFinalPrice()){
                $_minimalPriceDisplayValue = $_minimalPrice;
                if ($_weeeTaxAmount && $_weeeHelper->typeOfDisplay($product, array(0, 1, 4))){
                    $_minimalPriceDisplayValue = $_minimalPrice + $_weeeTaxAmount;
                }

                if (!$this->getUseLinkForAsLowAs()){
                    $priceXmlObj->addAttribute('as_low_as', $_coreHelper->currency($_minimalPriceDisplayValue, true, false));
                }
            }
        }
        /**
         * if (!$product->isGrouped()){
         */
        else {
            $_exclTax = $_taxHelper->getPrice($product, $_minimalPriceValue, $includingTax = null);
            $_inclTax = $_taxHelper->getPrice($product, $_minimalPriceValue, $includingTax = true);

            if ($this->getDisplayMinimalPrice() && $_minimalPriceValue){
                if ($_taxHelper->displayBothPrices()){
                    $priceXmlObj->addAttribute('starting_at_excluding_tax', $_coreHelper->currency($_exclTax, true, false));
                    $priceXmlObj->addAttribute('starting_at_including_tax', $_coreHelper->currency($_inclTax, true, false));
                }
                else {
                     $_showPrice = $_inclTax;
                    if (!$_taxHelper->displayPriceIncludingTax()) {
                        $_showPrice = $_exclTax;
                    }
                    $priceXmlObj->addAttribute('starting_at', $_coreHelper->currency($_showPrice, true, false));
                }
            }
        }
    }
}