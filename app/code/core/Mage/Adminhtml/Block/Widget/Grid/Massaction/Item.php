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
 * @category   Mage
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grid widget massaction single action item
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Ivan Chepurnyi <mitch@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Massaction_Item extends Mage_Adminhtml_Block_Widget
{
    protected $_massaction = null;

    /**
     * Set parent massaction block
     *
     * @param  Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract $massaction
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Item
     */
    public function setMassaction($massaction)
    {
        $this->_massaction = $massaction;
        return $this;
    }

    /**
     * Retrive parent massaction block
     *
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Abstract
     */
    public function getMassaction()
    {
        return $this->_massaction;
    }

    /**
     * Set additional action block for this item
     *
     * @param string|Mage_Core_Block_Abstract $block
     * @return Mage_Adminhtml_Block_Widget_Grid_Massaction_Item
     */
    public function setAdditionalActionBlock($block)
    {
        if(is_string($block)) {
            $block = $this->getLayout()->createBlock($block);
        } elseif(!($block instanceof Mage_Core_Block_Abstract)) {
            Mage::throwException('Unknown block type');
        }

        $this->setChild('additional_action', $block);
        return $this;
    }

    /**
     * Retrive additional action block for this item
     *
     * @return Mage_Core_Block_Abstract
     */
    public function getAdditionalActionBlock()
    {
        return $this->getChild('additional_action');
    }

    /**
     * Retrive additional action block HTML for this item
     *
     * @return string
     */
    public function getAdditionalActionBlockHtml()
    {
        return $this->getChildHtml('additional_action');
    }

} // Class Mage_Adminhtml_Block_Widget_Grid_Massaction_Item End