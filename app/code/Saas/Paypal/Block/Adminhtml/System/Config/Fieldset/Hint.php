<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Saas
 * @package     Saas_Paypal
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Renderer for PayPal banner in System Configuration
 *
 * @author      Magento Saas Team <saas@magentocommerce.com>
 */
class Saas_Paypal_Block_Adminhtml_System_Config_Fieldset_Hint
    extends Mage_Paypal_Block_Adminhtml_System_Config_Fieldset_Hint
{
    /**
     * Render fieldset html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $elementOriginalData = $element->getOriginalData();
        if (isset($elementOriginalData['help_url'])) {
            $this->setHelpUrl($elementOriginalData['help_url']);
            $this->setHtmlId($element->getId());
        }
        $js = '
            paypalToggleSolution = function(id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                if ($(this).hasClassName("open")) {
                    $$(".with-button button.button").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName("open")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }

            togglePaypalSolutionConfigureButton = function(button, enable) {
                var $button = $(button);
                $button.disabled = !enable;
                if ($button.hasClassName("disabled") && enable) {
                    $button.removeClassName("disabled");
                } else if (!$button.hasClassName("disabled") && !enable) {
                    $button.addClassName("disabled");
                }
            }

            // check store-view disabling Express Checkout
            document.observe("dom:loaded", function() {
                var ecButton = $$(".pp-method-express button.button")[0];
                var ecEnabler = $$(".paypal-ec-enabler.fd-enabled")[0];
                var ecBoardingEnabler = $$(".paypal-ec-boarding-enabler.fd-enabled")[0];
                if (typeof ecButton == "undefined" || (typeof ecEnabler != "undefined" && typeof ecBoardingEnabler != "undefined")) {
                    return;
                }
                var $ecButton = $(ecButton);
                $$(".with-button button.button").each(function(configureButton) {
                    if (configureButton != ecButton && !configureButton.disabled
                        && !$(configureButton).hasClassName("paypal-ec-separate")
                    ) {
                        togglePaypalSolutionConfigureButton(ecButton, false);
                    }
                });
            });
        ';
        return $this->toHtml() . $this->helper('Mage_Adminhtml_Helper_Js')->getScript($js);
    }
}
