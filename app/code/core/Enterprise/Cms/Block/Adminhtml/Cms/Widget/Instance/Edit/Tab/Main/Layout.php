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
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Cms Widget Instance page groups (predefined layouts group) to display on
 *
 * @category    Enterprise
 * @package     Enterprise_Cms
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Cms_Block_Adminhtml_Cms_Widget_Instance_Edit_Tab_Main_Layout
    extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('enterprise/cms/widget/instance/edit/layout.phtml');
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getBlockChooserUrl()
    {
        return $this->getUrl('*/*/blocks', array('_current' => true));
    }

    public function getCategoriesChooser()
    {
        $categories = $this->getLayout()
            ->createBlock('adminhtml/catalog_category_widget_chooser')
            ->setUseMassaction(true);
        return $categories->toHtml();
    }

    public function getProductsChooser()
    {
        $productsGrid = $this->getLayout()
            ->createBlock('adminhtml/catalog_product_widget_chooser')
            ->setUseMassaction(true)
            ->setSelectedProducts($this->_getSelectedProducts());
        return $productsGrid->toHtml();
    }

    protected function _getSelectedProducts()
    {
        return array('1');
        $products = $this->getWidgetInstance()->getProducts();
        return unserialize($products);
    }

    public function getProductTypesChooser()
    {
        $productTypes = $this->getLayout()
            ->createBlock('enterprise_cms/adminhtml_cms_widget_instance_edit_chooser_producttype');
        return $productTypes->toHtml();
    }

    public function getLayoutsChooser()
    {
        $layouts = $this->getLayout()
            ->createBlock('enterprise_cms/adminhtml_cms_widget_instance_edit_chooser_layout')
            ->setArea($this->getWidgetInstance()->getArea())
            ->setPackage($this->getWidgetInstance()->getPackage())
            ->setTheme($this->getWidgetInstance()->getTheme());
        return $layouts->toHtml();
    }

    public function getAddLayoutButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('enterprise_cms')->__('Add Layout'),
                'onclick'   => 'WidgetInstance.addPageGroup({})',
                'class'     => 'save'
            ));
        return $button->toHtml();
    }

    public function getRemoveLayoutButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label'     => Mage::helper('enterprise_cms')->__('Remove Layout'),
                'onclick'   => 'WidgetInstance.removePageGroup(this)',
                'class'     => 'save'
            ));
        return $button->toHtml();
    }
}