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
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Sales_Customer_Billing_AgreementController extends Mage_Core_Controller_Front_Action
{

    /**
     * View billing agreements
     *
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->renderLayout();
    }

    /**
     * Action predispatch
     *
     * Check customer authentication
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        if (!$this->_getSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }

    /**
     * View billing agreement
     *
     */
    public function viewAction()
    {
        if (!$this->_initAgreement()) {
            return;
        }
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $navigationBlock = $this->getLayout()->getBlock('customer_account_navigation');
        if ($navigationBlock) {
            $navigationBlock->setActive('sales/customer_billing_agreement/');
        }
        $this->renderLayout();
    }

    /**
     * Wizard start action
     *
     */
    public function startWizardAction()
    {
        $agreement = Mage::getModel('sales/billing_agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        if ($paymentCode) {
            try {
                $agreement->setStore(Mage::app()->getStore())
                    ->setMethodCode($paymentCode)
                    ->setReturnUrl(Mage::getUrl('*/*/returnWizard', array('payment_method' => $paymentCode)))
                    ->setCancelUrl(Mage::getUrl('*/*/cancelWizard', array('payment_method' => $paymentCode)));

                $this->_redirectUrl($agreement->initToken());
                return $this;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError('An error occurred during your request');
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * Wizard return action
     *
     */
    public function returnWizardAction()
    {
        $agreement = Mage::getModel('sales/billing_agreement');
        $paymentCode = $this->getRequest()->getParam('payment_method');
        $token = $this->getRequest()->getParam('token');
        if ($token && $paymentCode) {
            try {
                $agreement->setStore(Mage::app()->getStore())
                    ->setToken($token)
                    ->setMethodCode($paymentCode)
                    ->place(Mage::getSingleton('customer/session')->getCustomer());
                $this->_getSession()->addSuccess('Billing agreement has been created.');
                $this->_redirect('*/*/view', array('agreement' => $agreement->getAgreementId()));
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError('An error occurred during your request');
            }
            $this->_redirect('*/*/index');
        }
    }

    /**
     * Wizard cancel action
     *
     */
    public function cancelWizardAction()
    {
        $this->_redirect('*/*/index');
    }

    /**
     * Cancel action
     * Set billing agreement status to 'Canceled'
     *
     */
    public function cancelAction()
    {
        $agreement = $this->_initAgreement();
        if ($agreement && $agreement->canCancel()) {
            try {
                $agreement->cancel();
                $this->_getSession()->addNotice('The billing agreement has been canceled.');
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError('An error occurred during your request');
            }
        }
        $this->_redirect('*/*/view', array('_current' => true));
    }

    /**
     * Init billing agreement model from request
     *
     * @return Mage_Sales_Model_Billing_Agreement
     */
    protected function _initAgreement()
    {
        $agreementId = $this->getRequest()->getParam('agreement');
        if ($agreementId) {
            $billingAgreement = Mage::getModel('sales/billing_agreement')->load($agreementId);
            if (!$billingAgreement->getAgreementId()) {
                $this->_getSession()->addError($this->__('Wrong billing agreement ID specified.'));
                $this->_redirect('*/*/');
                return false;
            }
        }
        Mage::register('billing_agreement', $billingAgreement);
        return $billingAgreement;
    }

    /**
     * Retrieve customer session model
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }
}
