<?php 
/**
 * Newsletter subscribe controller 
 *
 * @package     Mage
 * @subpackage  Newsletter
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Ivan Chepurnyi <mitch@varien.com>
 */ 
 class Mage_Newsletter_SubscriberController extends Mage_Core_Controller_Front_Action 
 {
 	/**
 	 * Subscribe form 
 	 */
    public function indexAction() 
    {
        $this->loadLayout();
        $block = $this->getLayout()->createBlock('newsletter/subscribe','subscribe.content');
        $this->getLayout()->getMessagesBlock()->setMessages(
        	Mage::getSingleton('newsletter/session')->getMessages(true)
        );
        $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
        
        
    }
    
    /**
 	 * New subscription action
 	 */
    public function newAction() 
    {
    	$session = Mage::getSingleton('newsletter/session');
        $status = Mage::getModel('newsletter/subscriber')->subscribe($this->getRequest()->getParam('email'));
        
        if ($status instanceof Exception) {
        	$session->addError(__('There was a problem with the subscription').': '.$status);
        } else {
	        switch ($status) {
	        	case Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
	        		$session->addSuccess(__('Confirmation request has been sent'));
	        		break;
	        		
	        	case Mage_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
	        		$session->addSuccess(__('Successfully subscribed'));
	        		break;
	        }
        }
        
        $this->_redirect('*/*');
    }
    
    /**
     * Subscription confirm action
     */
    public function confirmAction() {
    	$id = (int) $this->getRequest()->getParam('id');
    	$subscriber = Mage::getModel('newsletter/subscriber')
    		->load($id);
    	
    	if($subscriber->getId() && $subscriber->getCode()) {
    		 if($subscriber->confirm($this->getRequest()->getParam('code'))) {
    		 	Mage::getSingleton('newsletter/session')->addSuccess('Your subscription has been successfully confirmed');	
    		 } else {
    		 	Mage::getSingleton('newsletter/session')->addError('Invalid subscription confirmation code');
    		 }
    	} else {
    		 Mage::getSingleton('newsletter/session')->addError('Invalid subscription id');
    	}
    	
    	$this->_redirect('*/*');
    }
    
    public function unsubscribeAction()
    {
    	$session = Mage::getSingleton('newsletter/session');
    	$result = Mage::getModel('newsletter/subscriber')->unsubscribe($this->getRequest()->getParam('email'));
    	
    	if ($result instanceof Exception) {
    		$session->addError(__('There was a problem with the unsubscription').': '.$status);
    	} else {
    		$session->addSuccess(__('You have been successfully unsubscribed'));
    	}
    	
    	$this->_redirect('*/*');
    }
 }