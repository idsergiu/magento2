<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_GoogleCheckout
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_GoogleCheckout_Model_Source_Shipping_Virtual_Schedule
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'OPTIMISTIC',  'label' => Mage::helper('googlecheckout')->__('Optimistic')),
            array('value' => 'PESSIMISTIC', 'label' => Mage::helper('googlecheckout')->__('Pessimistic')),
        );
    }
}