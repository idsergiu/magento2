<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

interface Mage_Backend_Model_Config_Structure_ElementInterface
{
    /**
     * Set element data
     *
     * @param array $data
     * @param string $scope
     */
    public function setData(array $data, $scope);

    /**
     * Retrieve element configuration
     *
     * @return array
     */
    public function getData();

    /**
     * Retrieve element id
     *
     * @return string
     */
    public function getId();

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getLabel();

    /**
     * Check whether element is visible
     *
     * @return bool
     */
    public function isVisible();
}

