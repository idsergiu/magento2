<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Product send to friend block
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
class Mage_Catalog_Block_Product_Send extends Mage_Catalog_Block_Product_Abstract
{
    public function __construct(){
        parent::__construct();
    }

    /**
     * Retrieve username for form field
     *
     * @return string
     */

    public function getUserName()
    {
        return Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getName();
    }

    public function getEmail()
    {
        return (string)Mage::getSingleton('Mage_Customer_Model_Session')->getCustomer()->getEmail();
    }

    public function getProductId()
    {
        return $this->getRequest()->getParam('id');
    }

    public function getMaxRecipients()
    {
        $sendToFriendModel = Mage::registry('send_to_friend_model');
        return $sendToFriendModel->getMaxRecipients();
    }
}
