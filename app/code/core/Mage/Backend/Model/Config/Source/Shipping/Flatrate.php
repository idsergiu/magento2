<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Backend_Model_Config_Source_Shipping_Flatrate
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=> Mage::helper('Mage_Backend_Helper_Data')->__('None')),
            array('value'=>'O', 'label'=>Mage::helper('Mage_Backend_Helper_Data')->__('Per Order')),
            array('value'=>'I', 'label'=>Mage::helper('Mage_Backend_Helper_Data')->__('Per Item')),
        );
    }
}
