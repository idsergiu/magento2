<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Backend_Model_Config_Source_Price_Scope
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('Mage_Core_Helper_Data')->__('Global')),
            array('value'=>'1', 'label'=>Mage::helper('Mage_Core_Helper_Data')->__('Website')),
        );
    }
}
