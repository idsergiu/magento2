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
 * Store grid column filter
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Victor Tihonchuk <victor@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Store extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * Retrieve System Store model
     *
     * @return Mage_Adminhtml_Model_System_Store
     */
    protected function _getStoreModel()
    {
        return Mage::getSingleton('adminhtml/system_store');
    }

    public function render(Varien_Object $row)
    {
        $origStores = $row->getData($this->getColumn()->getIndex());
        $stores = array();
        if (is_array($origStores)) {
            foreach ($origStores as $origStore) {
                if (is_numeric($origStore) && $origStore == 0) {
                    $stores[] = Mage::helper('adminhtml')->__('All Store Views');
                }
                elseif (is_numeric($origStore) && $storeName = $this->_getStoreModel()->getStoreName($origStore)) {
                    $stores[] = $storeName;
                }
                else {
                    $stores[] = $origStore;
                }
            }
        }
        else {
            if (is_numeric($origStores) && $storeName = $this->_getStoreModel()->getStoreName($origStores)) {
                $stores[] = $storeName;
            }
            elseif (is_numeric($origStores) && $origStores == 0) {
                $stores[] = Mage::helper('adminhtml')->__('All Store Views');
            }
            elseif (is_null($origStores) && $row->getStoreName()) {
                return $row->getStoreName() . ' ' . $this->__('[deleted]');
            }
            else {
                $stores[] = $origStores;
            }
        }

        return $stores ? join(', ', $stores) : '&nbsp;';
    }

}
