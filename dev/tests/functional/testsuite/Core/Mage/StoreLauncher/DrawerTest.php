<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_StoreLauncher
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Common Drawer tests
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_StoreLauncher_DrawerTest extends Mage_Selenium_TestCase
{
    /**
     * Set tile states in initial state before tests
     */
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
        //Products tile
        $this->navigate('manage_products');
        $this->runMassAction('Delete', 'all');
        $this->storeLauncherHelper()->setTileState('product', Core_Mage_StoreLauncher_Helper::$STATE_TODO);
        //Shipping tile
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->configure('ShippingMethod/shipping_disable');
        $this->storeLauncherHelper()->setTileState('shipping', Core_Mage_StoreLauncher_Helper::$STATE_TODO);
        //StoreInfo tile
        $this->systemConfigurationHelper()->configure('ShippingSettings/store_information_empty');
        $this->systemConfigurationHelper()->configure('General/general_default_emails');
        //Tax tile
        $this->storeLauncherHelper()->setTileState('tax', Core_Mage_StoreLauncher_Helper::$STATE_TODO);
    }

    /**
     * <p>Preconditions:</p>
     * <p>1. Login to Backend</p>
     * <p>2. Navigate to Store Launcher page</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
    }

    /**
     * <p>Drawer is displayed</p>
     *
     * @param string $tile Tile code
     * @test
     * @dataProvider tileNamesDataProvider
     * @TestlinkId TL-MAGE-6504
     */
    public function drawerIsDisplayed($tile)
    {
        /**
         * @var Core_Mage_StoreLauncher_Helper $helper
         */
        $helper = $this->storeLauncherHelper();
        $helper->openDrawer($tile);
        $this->assertTrue($this->controlIsVisible(self::UIMAP_TYPE_FIELDSET, 'common_drawer'));
        $this->assertTrue($this->controlIsVisible('button', 'close_drawer'));
        $this->assertTrue($this->controlIsVisible('button', 'save_my_settings'));
    }

    /**
     * <p>User can return to the Store Launcher page</p>
     *
     * @param string $tile Tile code
     * @test
     * @dataProvider tileNamesDataProvider
     * @TestlinkId TL-MAGE-6506
     */
    public function returnToTheStoreLauncherPage($tile)
    {
        /**
         * @var Core_Mage_StoreLauncher_Helper $helper
         */
        $helper = $this->storeLauncherHelper();
        $this->assertContains('tile-todo', $this->getControlAttribute(self::UIMAP_TYPE_FIELDSET, $tile, 'class'),
            'Tile state is not TODO. Tile code: ' . $tile);
        $helper->openDrawer($tile);
        $this->assertTrue($helper->closeDrawer(), 'Failed to close drawer');
        $this->assertContains('tile-todo', $this->getControlAttribute(self::UIMAP_TYPE_FIELDSET, $tile, 'class'),
            'Tile state changed. Tile code: ' . $tile);
    }

    /**
     * Data for drawerIsDisplayed|returnToTheStoreLauncherPage
     *
     * @return array
     */
    public function tileNamesDataProvider()
    {
        return array(
            array('bussines_info_tile'),
            array('tax_rules_tile'),
            array('payment_tile'),
            array('product_tile'), //TL-MAGE-6821, TL-MAGE-6822
            array('shipping_tile')
        );
    }
}