<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tab UIMap class
 *
 * @package     selenium
 * @subpackage  Mage_Selenium
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Selenium_Uimap_Tab extends Mage_Selenium_Uimap_Abstract
{
    /**
     * Tab ID
     *
     * @var string
     */
    protected $_tabId = '';

    /**
     * Construct a Uimap_Tab
     *
     * @param string $tabId Tab's ID
     * @param array $tabContainer Array of data that contains the specific tab
     */
    public function  __construct($tabId, array &$tabContainer)
    {
        $this->_tabId = $tabId;
        $this->_xPath = isset($tabContainer['xpath'])
            ? $tabContainer['xpath']
            : '';

        $this->_parseContainerArray($tabContainer);
    }

    /**
     * Get page ID
     *
     * @return string
     */
    public function getTabId()
    {
        return $this->_tabId;
    }

    /**
     * Get Fieldset structure by ID
     *
     * @param string $id Fieldset ID
     *
     * @return Mage_Selenium_Uimap_Fieldset|null
     */
    public function getFieldset($id)
    {
        return isset($this->_elements['fieldsets'])
            ? $this->_elements['fieldsets']->getFieldset($id)
            : null;
    }

    /**
     * Get Fieldset names in tab
     * @return array
     */
    public function getFieldsetNames()
    {
        if (!isset($this->_elements['fieldsets'])) {
            return array();
        }
        $names = array();
        foreach ($this->_elements['fieldsets'] as $fieldsetName => $content) {
            $names[] = $fieldsetName;
        }
        return $names;
    }

    /**
     * Get Tab Elements
     * @return array
     */
    public function getTabElements()
    {
        if (!isset($this->_elements['fieldsets'])) {
            return array();
        }
        $elements = array();
        foreach ($this->_elements['fieldsets'] as $fieldset) {
            foreach ($fieldset->_elements as $elementType => $elementsData) {
                if (array_key_exists($elementType, $elements)) {
                    foreach ($elementsData as $elementName => $elementLocator) {
                        if (array_key_exists($elementName, $elements[$elementType])) {
                            trigger_error(
                                '"' . $this->getTabId() . '" tab contains several "' . $elementType . '" with name "'
                                . $elementName . '"', E_USER_NOTICE);
                        }
                        $elements[$elementType][$elementName] = $elementLocator;
                    }
                } else {
                    $elements[$elementType] = $elementsData;
                }
            }
        }
        return $elements;
    }
}