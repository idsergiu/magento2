<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Selenium_Uimap_AbstractTest extends Unit_PHPUnit_TestCase
{
    /**
     * @covers Mage_Selenium_Uimap_Abstract::__call
     */
    public function test__call()
    {
        $uimapHelper = $this->_testConfig->getHelper('uimap');
        $uipage = $uimapHelper->getUimapPage('admin', 'create_customer');

        //Test getAll
        $buttons = $uipage->getAllButtons();
        $this->assertInstanceOf('Mage_Selenium_Uimap_ElementsCollection', $buttons);

        $fieldsets = $uipage->getMainForm()->getAllFieldsets();
        $this->assertInstanceOf('Mage_Selenium_Uimap_ElementsCollection', $fieldsets);
        $this->assertGreaterThanOrEqual(1, count($fieldsets));

        $buttons = $uipage->getMainForm()->getAllButtons();
        $this->assertInstanceOf('Mage_Selenium_Uimap_ElementsCollection', $buttons);

        //Test get
        $tabs = $uipage->getMainForm()->getTabs();
        $this->assertInstanceOf('Mage_Selenium_Uimap_TabsCollection', $tabs);
        $this->assertGreaterThanOrEqual(1, count($tabs));

        //Test find
        $field = $uipage->findField('first_name');
        $this->assertInternalType('string', $field);

        $message = $uipage->findMessage('success_saved_customer');
        $this->assertInternalType('string', $message);
    }
}