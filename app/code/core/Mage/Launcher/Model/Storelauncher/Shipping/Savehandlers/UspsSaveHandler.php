<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Launcher
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * USPS configuration save handler
 *
 * @category   Mage
 * @package    Mage_Launcher
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Launcher_Model_Storelauncher_Shipping_Savehandlers_UspsSaveHandler
    extends Mage_Launcher_Model_Tile_ConfigBased_ConfigDataSaveHandlerAbstract
{
    /**
     * Retrieve name of the related configuration section
     *
     * @return string
     */
    public function getRelatedConfigSection()
    {
        return 'carriers';
    }

    /**
     * Prepare configuration data for saving
     *
     * @param array $data
     * @return array prepared data
     * @throws Mage_Launcher_Exception
     */
    public function prepareData(array $data)
    {
        $preparedData = array();
        if (empty($data['groups']['usps']['fields']['userid']['value'])) {
            throw new Mage_Launcher_Exception('User ID is required.');
        }
        if (empty($data['groups']['usps']['fields']['password']['value'])) {
            throw new Mage_Launcher_Exception('Password is required.');
        }

        $preparedData['usps']['fields']['userid']['value'] =
            trim($data['groups']['usps']['fields']['userid']['value']);
        $preparedData['usps']['fields']['password']['value'] =
            trim($data['groups']['usps']['fields']['password']['value']);

        // enable USPS for checkout if needed
        $isMethodEnabled = empty($data['groups']['usps']['fields']['active']['value']) ? 0 : 1;
        $preparedData['usps']['fields']['active']['value'] = $isMethodEnabled;

        return $preparedData;
    }
}
