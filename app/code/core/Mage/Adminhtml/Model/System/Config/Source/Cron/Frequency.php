<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */


class Mage_Adminhtml_Model_System_Config_Source_Cron_Frequency
{

    protected static $_options;

    const CRON_DAILY    = 'D';
    const CRON_WEEKLY   = 'W';
    const CRON_MONTHLY  = 'M';

    public function toOptionArray()
    {
        if (!self::$_options) {
            self::$_options = array(
                array(
                    'label' => Mage::helper('Mage_Cron_Helper_Data')->__('Daily'),
                    'value' => self::CRON_DAILY,
                ),
                array(
                    'label' => Mage::helper('Mage_Cron_Helper_Data')->__('Weekly'),
                    'value' => self::CRON_WEEKLY,
                ),
                array(
                    'label' => Mage::helper('Mage_Cron_Helper_Data')->__('Monthly'),
                    'value' => self::CRON_MONTHLY,
                ),
            );
        }
        return self::$_options;
    }

}
