<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Observer for design editor module
 */
class Mage_DesignEditor_Model_Observer
{
    /**#@+
     * VDE specific layout update handles
     */
    const HANDLE_PAGE    = 'design_editor_page';
    const HANDLE_TOOLBAR = 'design_editor_toolbar';
    /**#@-*/

    /**
     * Renderer for wrapping html to be shown at frontend
     *
     * @var Mage_Core_Block_Template
     */
    protected $_wrappingRenderer = null;

    /**
     * Handler for 'controller_action_predispatch' event
     */
    public function preDispatch()
    {
        /* Deactivate the design editor, if the admin session has been already expired */
        if (!$this->_getSession()->isLoggedIn()) {
            $this->_getSession()->deactivateDesignEditor();
        }
        /* Apply custom design to the current page */
        if ($this->_getSession()->isDesignEditorActive() && $this->_getSession()->getSkin()) {
            Mage::getDesign()->setDesignTheme($this->_getSession()->getSkin());
        }
    }

    /**
     * Add the design editor toolbar to the current page
     *
     * @param Varien_Event_Observer $observer
     */
    public function addToolbar(Varien_Event_Observer $observer)
    {
        if (!$this->_getSession()->isDesignEditorActive()) {
            return;
        }

        /** @var $update Mage_Core_Model_Layout_Update */
        $update = $observer->getEvent()->getLayout()->getUpdate();
        $handles = $update->getHandles();
        $handle = reset($handles);
        if ($handle && $update->getPageHandleType($handle) == Mage_Core_Model_Layout_Update::TYPE_FRAGMENT) {
            $update->addHandle(self::HANDLE_PAGE);
        }
        $update->addHandle(self::HANDLE_TOOLBAR);
    }

    /**
     * Disable blocks HTML output caching
     */
    public function disableBlocksOutputCaching()
    {
        if (!$this->_getSession()->isDesignEditorActive()) {
            return;
        }
        Mage::app()->getCacheInstance()->banUse(Mage_Core_Block_Abstract::CACHE_GROUP);
    }

    /**
     * Set design_editor_active flag, which allows to load DesignEditor's CSS or JS scripts
     *
     * @param Varien_Event_Observer $observer
     */
    public function setDesignEditorFlag(Varien_Event_Observer $observer)
    {
        if (!$this->_getSession()->isDesignEditorActive()) {
            return;
        }
        /** @var $block Mage_Page_Block_Html_Head */
        $block = $observer->getEvent()->getLayout()->getBlock('head');
        if ($block) {
            $block->setDesignEditorActive(true);
        }
    }

    /**
     * Retrieve session instance for the design editor
     *
     * @return Mage_DesignEditor_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('Mage_DesignEditor_Model_Session');
    }

    /**
     * Wrap each element of a page that is being rendered, with a block-level HTML-element to highlight it in VDE
     *
     * Subscriber to event 'core_layout_render_element'
     *
     * @param Varien_Event_Observer $observer
     */
    public function wrapPageElement(Varien_Event_Observer $observer)
    {
        if (!$this->_getSession()->isDesignEditorActive()) {
            return;
        }

        if (!$this->_wrappingRenderer) {
            $this->_wrappingRenderer = Mage::getModel('Mage_DesignEditor_Block_Template', array(
                'template' => 'wrapping.phtml'
            ));
        }

        $event = $observer->getEvent();
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $event->getData('layout');
        $elementName = $event->getData('element_name');
        /** @var $transport Varien_Object */
        $transport = $event->getData('transport');

        $block = $layout->getBlock($elementName);
        $isVde = ($block && 0 === strpos(get_class($block), 'Mage_DesignEditor_Block_'));
        $isManipulationAllowed = $layout->isManipulationAllowed($elementName) && !$isVde;
        $isContainer = $layout->isContainer($elementName);

        if ($isManipulationAllowed || $isContainer) {
            $elementId = 'vde_element_' . rtrim(strtr(base64_encode($elementName), '+/', '-_'), '=');
            $this->_wrappingRenderer->setData(array(
                'element_id'    => $elementId,
                'element_title' => $layout->getElementProperty($elementName, 'label') ?: $elementName,
                'element_html'  => $transport->getData('output'),
                'is_manipulation_allowed'  => $isManipulationAllowed,
                'is_container'  => $isContainer,
                'element_name'  => $elementName,
            ));
            $transport->setData('output', $this->_wrappingRenderer->toHtml());
        }

        /* Inject toolbar at the very beginning of the page */
        if ($elementName == 'after_body_start') {
            $elementHtml = $transport->getData('output');
            $toolbarHtml = $layout->renderElement('design_editor_toolbar');
            $transport->setData('output', $toolbarHtml . $elementHtml);
        }
    }

    /**
     * Deactivate the design editor
     */
    public function adminSessionUserLogout()
    {
        $this->_getSession()->deactivateDesignEditor();
    }
}
