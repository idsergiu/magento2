<?php
class Mage_Adminhtml_Tax_RuleController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(__('Tax Rules'), __('Tax Rules Title'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_add', 'tax_rule_toolbar')
                ->assign('createUrl', Mage::getUrl('adminhtml/tax_rule/add'))
                ->assign('header', __('Tax Rules'))
            )
            ->_addContent($this->getLayout()->createBlock('adminhtml/tax_rule_grid', 'tax_rule_grid'))
            ->renderLayout();
    }

    public function addAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(__('Tax Rules'), __('Tax Rules Title'), Mage::getUrl('adminhtml/tax_rules'))
            ->_addBreadcrumb(__('New Tax Rule'), __('New Tax Rule Title'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_save')
                ->assign('header', __('New Tax Rule'))
                ->assign('form', $this->getLayout()->createBlock('adminhtml/tax_rule_form_add'))
            )
            ->renderLayout();
    }

    public function saveAction()
    {
        if( $postData = $this->getRequest()->getPost() ) {
            try {
                $ruleModel = Mage::getSingleton('tax/rule');
                $ruleModel->setData($postData);
                $ruleModel->save();
                $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
            } catch (Exception $e) {
                # FIXME !!!!
            }
        }
    }

    public function editAction()
    {
        $this->_initAction()
            ->_addBreadcrumb(__('Tax Rules'), __('Tax Rules Title'), Mage::getUrl('adminhtml/tax_rule'))
            ->_addBreadcrumb(__('Edit Tax Rule'), __('Edit Tax Rule Title'))
            ->_addContent(
                $this->getLayout()->createBlock('adminhtml/tax_rule_toolbar_save')
                    ->assign('header', __('Edit Tax Rule'))
                    ->assign('form', $this->getLayout()->createBlock('adminhtml/tax_rule_form_add'))
            )
            ->renderLayout();
    }

    public function deleteAction()
    {
        try {
            $ruleModel = Mage::getSingleton('tax/rule');
            $ruleModel->setRuleId($this->getRequest()->getParam('rule'));
            $ruleModel->delete();
            $this->getResponse()->setRedirect(Mage::getUrl("*/*/"));
        } catch (Exception $e) {
            # FIXME
        }
    }

    /**
     * Initialize action
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout('baseframe')
            ->_setActiveMenu('sales/tax/tax_rule')
            ->_addBreadcrumb(__('Sales'), __('Sales Title'))
            ->_addBreadcrumb(__('Tax'), __('Tax Title'))
            ->_addLeft($this->getLayout()->createBlock('adminhtml/tax_tabs', 'tax_tabs')->setActiveTab('tax_rule'))
        ;
        return $this;
    }

}
