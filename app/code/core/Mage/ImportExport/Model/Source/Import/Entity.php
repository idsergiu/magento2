<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Source import entity model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Source_Import_Entity
{
    /**
     * Prepare and return array of import entities ids and their names
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();
        $entities = Mage_ImportExport_Model_Import::CONFIG_KEY_ENTITIES;
        $comboOptions = Mage_ImportExport_Model_Config::getModelsComboOptions($entities);

        foreach ($comboOptions as $option) {
           $options[] = $option;
        }
        return $options;
    }
}
