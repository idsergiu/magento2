<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Config edit page
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Config_Edit extends Mage_Adminhtml_Block_Widget
{
    const DEFAULT_SECTION_BLOCK = 'Mage_Adminhtml_Block_System_Config_Form';

    protected $_section;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('system/config/edit.phtml');

        $sectionCode = $this->getRequest()->getParam('section');
        $sections = Mage::getSingleton('Mage_Adminhtml_Model_Config')->getSections();

        $this->_section = $sections->$sectionCode;

        $this->setTitle((string)$this->_section->label);
        $this->setHeaderCss((string)$this->_section->header_css);
    }

    protected function _prepareLayout()
    {
        $this->addChild('save_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'     => Mage::helper('Mage_Adminhtml_Helper_Data')->__('Save Config'),
            'class' => 'save',
            'data_attr'  => array(
                'widget-button' => array('event' => 'save', 'related' => '#config-edit-form')
            )
        ));
        return parent::_prepareLayout();
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('_current'=>true));
    }

    public function initForm()
    {
        /*
        $this->setChild('dwstree',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_System_Config_Dwstree')
                ->initTabs()
        );
        */

        $blockName = (string)$this->_section->frontend_model;
        if (empty($blockName)) {
            $blockName = self::DEFAULT_SECTION_BLOCK;
        }
        $this->setChild('form',
            $this->getLayout()->createBlock($blockName)
                ->initForm()
        );
        return $this;
    }


}
