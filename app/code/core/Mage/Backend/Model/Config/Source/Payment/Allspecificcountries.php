<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Backend_Model_Config_Source_Payment_Allspecificcountries
{
    public function toOptionArray()
    {
        return array(
            array('value'=>0, 'label'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('All Allowed Countries')),
            array('value'=>1, 'label'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Specific Countries')),
        );
    }
}
