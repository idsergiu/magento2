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
 * @package    Mage_Checkout
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Checkout_TestController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        $quote = Mage::getModel('sales/quote');
        echo "<pre>".print_r($quote,1)."</pre>";

    }

    public function createEntitiesAction()
    {
        $setup = Mage::getModel('sales_entity/setup', 'sales_setup');
        $setup->installEntities($setup->getDefaultEntities());
    }

    public function mailAction()
    {
        $order = Mage::getModel('sales/order')->load(23);
        $billing = $order->getBillingAddress();
        Mage::getModel('sales/email_template')
            ->sendTransactional('new_order', $billing->getEmail(), $billing->getName(), array('order'=>$order, 'billing'=>$billing));
    }

    public function trackupsAction()
    {
         $carrier = Mage::getModel('Usa/shipping_carrier_ups');
         $result = $carrier->getTracking(array('1Z020FF91260351815','1Z020FF90360351074','1ZV953560349447013')); // ups
         echo "<pre>";
         print_r($result);
         echo "</pre>";
         exit;
    }

    public function trackuspsAction()
    {
         $carrier = Mage::getModel('Usa/shipping_carrier_usps');
          $result = $carrier->getTracking(array('EQ944289016US','EQ944290195US')); // usps
         echo "<pre>";
         print_r($result);
         echo "</pre>";
         exit;
    }

    public function trackfedexAction()
    {
         $carrier = Mage::getModel('Usa/shipping_carrier_fedex');
          $result = $carrier->getTracking(array('749059830009648','749059830009358','1111111111111111'));
         echo "<pre>";
         print_r($result);
         echo "</pre>";
         exit;
    }

    public function trackdhlAction()
    {
         $carrier = Mage::getModel('Usa/shipping_carrier_dhl');
          $result = $carrier->getTracking(array('1231230011','2342340011','7897890011','8185568204'));
         echo "<pre>";
         print_r($result);
         echo "</pre>";
         exit;
    }

    public function trackingAction()
    {
        echo "tracking start:";
        $carrier= Mage::getModel('Usa/shipping_carrier_ups');
//        $carrier->getTracking(array('1231230011','2342340011','7897890011')); // dhl
//        $carrier->getTracking(array('EQ944289016US','EQ944290195US')); // usps
            $carrier->getTracking(array('1Z020FF91260351815','1Z020FF90360351074','1ZV953560349447013')); // ups
//        $carrier->getTracking(array('749059830009648','749059830009358')); // fedex
        exit;
    }

    public function paymentAction()
    {


        //payflow testing
        /*
        $payment= Mage::getModel('Paygate/payflow_pro');
        //Mage_Payment_Model_Info
        $paymentinfo= Mage::getModel('Payment/info');
        $paymentinfo->setCcTransId('V19A0CEB3717');
        */

        //authorizenet testing
        //$payment= Mage::getModel('Paygate/authorizenet');

        //paypalexpress testing
         $payment= Mage::getModel('paypal/direct');
        //Mage_Payment_Model_Info
        $paymentinfo= Mage::getModel('Payment/info');
        $paymentinfo->setCcTransId('1UB259449R218042D');
        $paymentinfo->setCcTransId('75D52621K39021610');

        $payment->canVoid($paymentinfo);

echo "<pre><hr>";
echo "AFTER CAN VOID:";
print_r($paymentinfo->getData());

        if($paymentinfo->getStatus()==Mage_Payment_Model_Method_Abstract::STATUS_VOID){
                //void the transaction
                $payment->void($paymentinfo);
           echo "AFTER VOID:";
           print_r($paymentinfo->getData());
        }

        if($paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_ERROR &&
            $paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_VOID &&
            $paymentinfo->getStatus()!=Mage_Payment_Model_Method_Abstract::STATUS_SUCCESS){
            $paymentinfo->setAmount('0.6');
            //credit the transaction
            $payment->refund($paymentinfo);
            echo "AFTER CREDIT2:";
            print_r($paymentinfo->getData());
        }

        if($paymentinfo->getStatus()==Mage_Payment_Model_Method_Abstract::STATUS_ERROR){
           //error in retreiving transaction*
           echo "#####ERROR:".$paymentinfo->getStatusDescription();
        }
echo "<hr>";





    }
}