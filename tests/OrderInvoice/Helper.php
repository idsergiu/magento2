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
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrderInvoice_Helper extends Mage_Selenium_TestCase
{

    /**
     * Provides partial or full invoice
     *
     * @param array $invoiceData
     */
    public function createPartialInvoiceAndVerify(array $invoiceData)
    {
        $invoiceData = $this->arrayEmptyClear($invoiceData);
        $this->clickButton('invoice');

        $verify = array();
        foreach ($invoiceData as $product => $options) {
            if (is_array($options)) {
                $sku = (isset($options['invoice_product_sku'])) ? $options['invoice_product_sku'] : NULL;
                $productQty = (isset($options['invoice_product_qty'])) ? $options['invoice_product_qty'] : '%noValue%';
                if ($sku) {
                    $verify[$sku] = $productQty;
                    $this->addParameter('sku', $sku);
                    $this->fillForm(array('qty_to_invoice' => $productQty));
                }
            }
        }
        $buttonXpath = $this->_getControlXpath('button', 'update_qty');
        if ($this->isElementPresent($buttonXpath . "[not(@disabled)]")) {
            $this->click($buttonXpath);
            $this->pleaseWait();
        }
        $this->clickButton('submit_invoice');
        $this->assertTrue($this->successMessage('success_creating_invoice'), $this->messages);
        foreach ($verify as $productSku => $qty) {
            if ($qty == '%noValue%') {
                continue;
            }
            $this->addParameter('sku', $productSku);
            $this->addParameter('invoicedQty', $qty);
            $xpathInvoiced = $this->_getControlXpath('field', 'qty_invoiced');
            $this->assertTrue($this->isElementPresent($xpathInvoiced),
                    'Qty of invoiced products is incorrect at the orders form');
        }
    }

}
