<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Enterprise_Rma
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Enterprise_Rma_Block_Adminhtml_Create_OrderTest extends Mage_Backend_Area_TestCase
{
    public function testToHtml()
    {
        $utility = new Mage_Core_Utility_Layout($this);
        $layoutArguments = array_merge($utility->getLayoutDependencies(), array('area' => 'adminhtml'));
        $layout = $utility->getLayoutFromFixture(
            __DIR__ . '/../../../_files/chooseorder.xml',
            $layoutArguments
        );
        $layout->getUpdate()->addHandle('adminhtml_rma_chooseorder')->load();
        $layout->generateXml()->generateElements();
        $layout->addOutputElement('rma_create_order');
        Mage::getDesign()->setArea('adminhtml');
        $this->assertContains('<div id="enterprise_rma_rma_create_order_grid">', $layout->getOutput());
    }
}
