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
 * Adminhtml catalog product action attribute update controller
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Ivan Chepurnyi <mitch@varien.com>
 */
class Mage_Adminhtml_Catalog_Product_Action_AttributeController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Catalog');
    }
    
    public function editAction()
    {
        if(!is_array($this->_getHelper()->getProductIds())) {
            $this->_getSession()->addError($this->__('Please select products for attributes update'));
            $this->_redirect('*/catalog_product/index', array('_current'=>true));
            return;
        }

        if($countNotInStore = count($this->_getHelper()->getProductsNotInStoreIds())) {
            $this->_getSession()->addWarning($this->__('There is %d product(s) that will be not updated for selected store', $countNotInStore));
        }

        $this->loadLayout();
        $this->_addLeft(
                $this->getLayout()->createBlock('adminhtml/store_switcher')
                    ->setDefaultStoreName($this->__('Default Values'))
                    ->setSwitchUrl(Mage::getUrl('*/*/*', array('_current'=>true, 'store'=>null)))
        );
        $this->_addLeft($this->getLayout()->createBlock('adminhtml/catalog_product_edit_action_attribute_tabs', 'attributes_tabs'));
        $this->_addContent($this->getLayout()->createBlock('adminhtml/catalog_product_edit_action_attribute'));
        $this->renderLayout();
    }

    public function saveAction()
    {
        if(!is_array($this->_getHelper()->getProductIds())) {
            $this->_getSession()->addError($this->__('Please select products for attributes update'));
            $this->_redirect('*/catalog_product/index', array('_current'=>true));
            return;
        }

        $data = $this->getRequest()->getParam('attributes');
        if(!is_array($data)) {
            $data = array();
        }

        $productsNotInStore = $this->_getHelper()->getProductsNotInStoreIds();
        try {
            foreach($this->_getHelper()->getProducts() as $product) {
                if(in_array($product->getId(), $productsNotInStore)) {
                    continue;
                }

                $storeIds = $product->getResource()->getStoreIds($product);

                $product->setStoreId((int) $this->getRequest()->getParam('store', 0))
                    ->load($product->getId())
                    ->addData($data)
                    ->setStoreId((int) $this->getRequest()->getParam('store', 0));

                if ($product->getStoreId() == 0) {
                    $product->setPostedStores($storeIds);
                } else {
                    $product->setPostedStores(array($product->getStoreId()=>$product->getStoreId()));
                }

                $product->save();
            }

            $this->_getSession()->addSuccess($this->__('Attributes of %d product(s) has been successfully updated',
                                             count($this->_getHelper()->getProducts())-count($productsNotInStore)));
        }
        catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        }
        catch (Exception $e) {
            $this->_getSession()->addError($this->__('There was an error while updating product(s) attributes'));
        }

        $this->_redirect('*/catalog_product/', array('store'=>$this->getRequest()->getParam('store', 0)));
    }

    protected function _getCatalogHelper()
    {
        return Mage::helper('catalog');
    }

    protected function _getHelper()
    {
        return Mage::helper('adminhtml/catalog_product_edit_action_attribute');
    }
} // Class Mage_Adminhtml_Catalog_Product_Action_AttributeController End