<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Customer
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Customer_Block_Account_Dashboard_Hello extends Mage_Core_Block_Template
{

    public function getCustomerName()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getName();
    }

}
