<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_DesignEditor
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

Magento_Test_Helper_Bootstrap::getInstance()->reinitialize(array(
    Mage::PARAM_APP_DIRS => array(
        Mage_Core_Model_Dir::THEMES => dirname(__DIR__) . '/design'
    )
));

Mage::app()->loadAreaPart(Mage_Core_Model_App_Area::AREA_ADMINHTML, Mage_Core_Model_App_Area::PART_CONFIG);

/** @var $registration Mage_Core_Model_Theme_Registration */
$registration = Mage::getModel('Mage_Core_Model_Theme_Registration');
$registration->register(
    __DIR__,
    implode(DIRECTORY_SEPARATOR, array('*', '*', '*', 'theme.xml'))
);

