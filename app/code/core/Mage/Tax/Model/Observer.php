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
 * @category   Mage
 * @package    Mage_Tax
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tax Event Observer
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Observer
{
    /**
     * Put quote address tax information into order
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesEventConvertQuoteAddressToOrder(Varien_Event_Observer $observer)
    {
        $address = $observer->getEvent()->getAddress();
        $order = $observer->getEvent()->getOrder();

        $order->setAppliedTaxes($address->getAppliedTaxes());
        $order->setConvertingFromQuote(true);
    }

    /**
     * Save order tax information
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesEventOrderAfterSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getConvertingFromQuote()) {
            return;
        }

        $taxes = $order->getAppliedTaxes();
        foreach ($taxes as $row) {
            //throw new Exception(print_r($tax, true));
            foreach ($row['rates'] as $tax) {
                $data = array(
                            'order_id'=>$order->getId(),
                            'code'=>$tax['code'],
                            'title'=>$tax['title'],
                            'percent'=>$tax['percent'],
                            'priority'=>$tax['priority'],
                            'position'=>$tax['position'],
                            'amount'=>$row['amount'],
                            );

                Mage::getModel('sales/order_tax')->setData($data)->save();
            }
        }
    }
}