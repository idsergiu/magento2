<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Adminhtml_Block_Tax_Class_EditTest extends PHPUnit_Framework_TestCase
{
    public function testSetClassType()
    {
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout');
        /** @var $block Mage_Adminhtml_Block_Tax_Class_Edit */
        $block = $layout->createBlock('Mage_Adminhtml_Block_Tax_Class_Edit', 'block');
        $childBlock = $block->getChildBlock('form');

        $expected = 'a_class_type';
        $this->assertNotEquals($expected, $childBlock->getClassType());
        $block->setClassType($expected);
        $this->assertEquals($expected, $childBlock->getClassType());
    }
}
