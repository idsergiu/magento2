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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product controller
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @module     Catalog
 */
class Mage_Catalog_ProductController extends Mage_Core_Controller_Front_Action
{
    protected function _initProduct()
    {
        $categoryId = (int) $this->getRequest()->getParam('category', false);
        $productId  = (int) $this->getRequest()->getParam('id');

        $product = Mage::getModel('catalog/product')
            ->load($productId);

        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            Mage::register('current_category', $category);
        }
        Mage::register('current_product', $product);
        Mage::register('product', $product); // this need remove after all replace
    }

	public function viewAction()
    {
        $this->_initProduct();
        $product = Mage::registry('product');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_forward('noRoute');
            return;
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle('PRODUCT_'.$product->getId());

        $this->loadLayoutUpdates();

        $update->addUpdate($product->getCustomLayoutUpdate());

        $this->generateLayoutXml()->generateLayoutBlocks();

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('tag/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function galleryAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function sendAction(){
        $this->_initProduct();
        $product = Mage::registry('product');
        if (!$product->getId() || !$product->isVisibleInCatalog()) {
            $this->_forward('noRoute');
            return;
        }

        $allowGuestToSendFriend = Mage::getStoreConfig('sendfriend/email/allow_guest');
        $userIsLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();

        if (!$userIsLoggedIn && !$allowGuestToSendFriend) {
            $this->_redirect('/');
            return;
        }

        $update = $this->getLayout()->getUpdate();
        $update->addHandle('default');
        $this->addActionLayoutHandles();

        $update->addHandle('PRODUCT_'.$product->getId());

        $this->loadLayoutUpdates();

        $update->addUpdate($product->getCustomLayoutUpdate());

        $this->generateLayoutXml()->generateLayoutBlocks();

        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('tag/session');
        $this->_initLayoutMessages('checkout/session');
        $this->renderLayout();
    }

    public function sendmailAction()
    {
        $recipients_email = array();
        if($this->getRequest()->getPost() && $this->getRequest()->getParam('id')) {
            $sender = $this->getRequest()->getParam('sender');
            $recipients = $this->getRequest()->getParam('recipients');
            $recipients_email = $recipients['email'];
            $recipients_email = array_unique($recipients_email);
            $recipients_name = $recipients['name'];
            $product = Mage::getModel('catalog/product')
              ->load((int)$this->getRequest()->getParam('id'));

            $errors = array();
            foreach ($recipients_email as $key=>$emailTo) {
                if($emailTo){
                    $emailModel = Mage::getModel('core/email_template');
                    $emailTo = trim($emailTo);
                    $recipient = $recipients_name[$key];
                    $templ = Mage::getStoreConfig('sendfriend/email/template');
                    if(!$templ){

                        return false;
                    }
                	$emailModel->load(Mage::getStoreConfig('sendfriend/email/template'));
                	if (!$emailModel->getId()) {
                		 Mage::getSingleton('catalog/session')->addError(Mage::helper('catalog')->__('Invalid transactional email code'));
                	}
                	$emailModel->setSenderName(strip_tags($sender['name']));
                	$emailModel->setSenderEmail(strip_tags($sender['email']));

                	$vars = array(
                	   'senderName' => strip_tags($sender['name']),
                	   'senderEmail' => strip_tags($sender['email']),
                	   'receiverName' => strip_tags($recipient),
                	   'receiverEmail' => strip_tags($emailTo),
                	   'product' => $product,
                	   'message' => strip_tags($sender['message'])
                	   );
                	if(!$emailModel->send(strip_tags($emailTo), strip_tags($recipient), $vars)){
                	    $errors[] = $emailTo;
                	}

                }
            }
            if(count($errors)>0){
                foreach ($errors as $val) {
                    Mage::getSingleton('catalog/session')->addError(Mage::helper('catalog')->__('Email to %s does not sent.'),$val);
                }
                $this->_redirectError(Mage::getURL('catalog/product/send',array('id'=>$product->getId())));
            } else {
                Mage::getSingleton('catalog/session')->addSuccess(Mage::helper('catalog')->__('Link to a friend was sent.'));
                $this->_redirectSuccess($product->getProductUrl());
            }
        }
    }
}
