<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Reward admin customer controller
 *
 * @category    Enterprise
 * @package     Enterprise_Reward
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Reward_Adminhtml_Customer_RewardController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check if module functionality enabled
     *
     * @return Enterprise_Reward_Adminhtml_Reward_RateController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('Enterprise_Reward_Helper_Data')->isEnabled() && $this->getRequest()->getActionName() != 'noroute') {
            $this->_forward('noroute');
        }
        return $this;
    }

    /**
     * History Ajax Action
     */
    public function historyAction()
    {
        $customerId = $this->getRequest()->getParam('id', 0);
        $history = $this->getLayout()
            ->createBlock('Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History', '',
                array('customer_id' => $customerId));
        $this->getResponse()->setBody($history->toHtml());
    }

    /**
     * History Grid Ajax Action
     *
     */
    public function historyGridAction()
    {
        $customerId = $this->getRequest()->getParam('id', 0);
        $history = $this->getLayout()
            ->createBlock('Enterprise_Reward_Block_Adminhtml_Customer_Edit_Tab_Reward_History_Grid', '',
                array('customer_id' => $customerId));
        $this->getResponse()->setBody($history->toHtml());
    }

    /**
     *  Delete orphan points Action
     */
    public function deleteOrphanPointsAction()
    {
        $customerId = $this->getRequest()->getParam('id', 0);
        if ($customerId) {
            try {
                Mage::getModel('enterprise_reward/reward')
                    ->deleteOrphanPointsByCustomer($customerId);
                $this->_getSession()
                    ->addSuccess(Mage::helper('Enterprise_Reward_Helper_Data')->__('The orphan points have been removed.'));
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/customer/edit', array('_current' => true));
    }

    /**
     * Acl check for admin
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')
            ->isAllowed(Enterprise_Reward_Helper_Data::XML_PATH_PERMISSION_BALANCE);
    }
}
