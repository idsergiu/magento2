<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Theme customisation interface
 */
interface Mage_Core_Model_Theme_Customisation_Interface
{
    /**
     * Setter for data for save
     *
     * @param mixed $data
     */
    public function setDataForSave($data);

    /**
     * Return collection customisation form theme
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Resource_Db_Collection_Abstract
     */
    public function getCollectionByTheme(Mage_Core_Model_Theme $theme);

    /**
     * Save data
     *
     * @param Mage_Core_Model_Theme $theme
     */
    public function saveData(Mage_Core_Model_Theme $theme);
}
