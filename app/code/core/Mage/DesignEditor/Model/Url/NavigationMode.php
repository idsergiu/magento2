<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Navigation mode design editor url model
 */
class Mage_DesignEditor_Model_Url_NavigationMode extends Mage_Core_Model_Url
{
    /**
     * VDE helper
     *
     * @var Mage_DesignEditor_Helper_Data
     */
    protected $_helper;

    /**
     * Constructor
     *
     * @param Mage_DesignEditor_Helper_Data $helper
     * @param array $data
     */
    public function __construct(Mage_DesignEditor_Helper_Data $helper, array $data = array())
    {
        $this->_helper = $helper;
        parent::__construct($data);
    }

    /**
     * Retrieve route path
     *
     * @param array $routeParams
     * @return string
     */
    public function getRoutePath($routeParams = array())
    {
        return $this->_helper->getFrontName() . '/' . parent::getRoutePath($routeParams);
    }
}
