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
 * @package     Mage_Paypal
 * @copyright   Copyright (c) 2010 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * PayPal Settlement Reports Controller
 *
 * @category    Mage
 * @package     Mage_Paypal
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Paypal_Adminhtml_Paypal_ReportsController extends Mage_Adminhtml_Controller_Action
{

    /**
     * Grid renderer
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('paypal/adminhtml_settlement_grid', 'settlementGrid'))
            ->renderLayout();
    }

    /**
     * Ajax callback for grid actions
     */
    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('paypal/adminhtml_settlement_grid', 'settlementGrid')->toHtml()
        );
    }

    /**
     * View transaction details
     */
    public function detailsAction()
    {
        $rowId = $this->getRequest()->getParam('id');
        $row = Mage::getModel('paypal/report_settlement_row')->load($rowId);
        if (!$row->getId()) {
            $this->_redirect('*/*/');
            return;
        }
        Mage::register('current_transaction', $row);
        $this->_initAction()
            ->_title($this->__('View Transaction'))
            ->_addContent($this->getLayout()->createBlock('paypal/adminhtml_settlement_details', 'settlementDetails'))
            ->renderLayout();
    }

    /**
     * Manual reports fetching
     */
    public function fetchAction()
    {
        try {
            // Observer model should be rewritted and fixed - this is temporary call and should be removed in future
            Mage::getModel('paypal/observer')->fetchReports();
            $this->_getSession()->addSuccess(Mage::helper('paypal')->__('Reports have been fetched successfully.'));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            Mage::logException($e);
        }

        $this->_redirect('*/*/index');
    }

    /**
     * Initialize titles, navigation
     * @return Mage_Paypal_Adminhtml_Paypal_ReportsController
     */
    protected function _initAction()
    {
        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('PayPal Settlement Reports'));
        $this->loadLayout()
            ->_setActiveMenu('report/sales')
            ->_addBreadcrumb(Mage::helper('paypal')->__('Reports'), Mage::helper('paypal')->__('Reports'))
            ->_addBreadcrumb(Mage::helper('paypal')->__('Sales'), Mage::helper('paypal')->__('Sales'))
            ->_addBreadcrumb(Mage::helper('paypal')->__('PayPal Settlement Reports'), Mage::helper('paypal')->__('PayPal Settlement Reports'));
        return $this;
    }
}
