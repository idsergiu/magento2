<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Export controller
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Adminhtml_ExportController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Custom constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        // Define module dependent translate
        $this->setUsedModuleName('Mage_ImportExport');
    }

    /**
     * Initialize layout.
     *
     * @return Mage_ImportExport_Adminhtml_ExportController
     */
    protected function _initAction()
    {
        $this->_title($this->__('Import/Export'))
            ->loadLayout()
            ->_setActiveMenu('Mage_ImportExport::system_convert_export');

        return $this;
    }

    /**
     * Check access (in the ACL) for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Backend_Model_Auth_Session')->isAllowed('Mage_ImportExport::export');
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return Mage_ImportExport_Adminhtml_ExportController
     */
    public function exportAction()
    {
        if ($this->getRequest()->getPost(Mage_ImportExport_Model_Export::FILTER_ELEMENT_GROUP)) {
            try {
                /** @var $model Mage_ImportExport_Model_Export */
                $model = Mage::getModel('Mage_ImportExport_Model_Export');
                $model->setData($this->getRequest()->getParams());

                return $this->_prepareDownloadResponse(
                    $model->getFileName(),
                    $model->export(),
                    $model->getContentType()
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::logException($e);
                $this->_getSession()->addError($this->__('No valid data sent'));
            }
        } else {
            $this->_getSession()->addError($this->__('No valid data sent'));
        }
        return $this->_redirect('*/*/index');
    }

    /**
     * Index action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_initAction()
            ->_title($this->__('Export'))
            ->_addBreadcrumb($this->__('Export'), $this->__('Export'));

        $this->renderLayout();
    }

    /**
     * Get grid-filter of entity attributes action.
     *
     * @return void
     */
    public function getFilterAction()
    {
        $data = $this->getRequest()->getParams();
        if ($this->getRequest()->isXmlHttpRequest() && $data) {
            try {
                $this->loadLayout();

                /** @var $attrFilterBlock Mage_ImportExport_Block_Adminhtml_Export_Filter */
                $attrFilterBlock = $this->getLayout()->getBlock('export.filter');
                /** @var $export Mage_ImportExport_Model_Export */
                $export = Mage::getModel('Mage_ImportExport_Model_Export');
                $export->setData($data);

                $export->filterAttributeCollection(
                    $attrFilterBlock->prepareCollection(
                        $export->getEntityAttributeCollection()
                    )
                );
                $this->renderLayout();
                return;
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        } else {
            $this->_getSession()->addError($this->__('No valid data sent'));
        }
        $this->_redirect('*/*/index');
    }
}
