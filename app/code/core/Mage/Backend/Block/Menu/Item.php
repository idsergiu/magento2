<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Backend menu item block
 *
 * @category   Mage
 * @package    Mage_Backend
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Block_Menu_Item extends Mage_Backend_Block_Template
{
    /**
     * Initialize template
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Mage_Backend::menu/item.phtml');
    }

    /**
     * Check whether given item is currently selected
     *
     * @param Mage_Backend_Model_Menu_Item $item
     * @return bool
     */
    public function isItemActive(Mage_Backend_Model_Menu_Item $item)
    {
        return ($this->getActive() == $item->getAction())
            || (strpos($this->getActive(), $item->getFullPath().'/')===0);
    }

    /**
     * Current menu item is last
     * @return bool
     */
    public function isLast()
    {
        return $this->getLevel() == 0 && (int)$this->getContainerRenderer()
            ->getMenuModel()
            ->getChildren()
            ->isLast($this->getItem());
    }
}
