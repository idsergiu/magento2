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
 * @package    Mage_Adminhtml
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * customers defined options
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Options_Option extends Mage_Adminhtml_Block_Widget
{
    protected $_product;

    protected $_productInstance;

    protected $_name = 'product[options]';
    protected $_id = 'product_option';

    protected $_values;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('catalog/product/edit/options/option.phtml');
    }

    /**
     * Enter description here...
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_productInstance) {
            if ($product = Mage::registry('product')) {
                $this->_productInstance = $product;
            } else {
                $this->_productInstance = Mage::getSingleton('catalog/product');
            }
        }

        return $this->_productInstance;
    }

    public function setProduct($product)
    {
        $this->_productInstance = $product;
        return $this;
    }

    public function getFieldName()
    {
        return $this->_name;
    }

    public function getFieldId()
    {
        return $this->_id;
    }

    protected function _prepareLayout()
    {
        $this->setChild('delete_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label' => Mage::helper('catalog')->__('Delete'),
                    'class' => 'delete delete-product-option',
                ))
        );

        //text
        $this->setChild('text_option_type',
            $this->getLayout()->createBlock(
                'adminhtml/catalog_product_edit_tab_options_type_text'
            )
        );

        //file
        $this->setChild('file_option_type',
            $this->getLayout()->createBlock(
                'adminhtml/catalog_product_edit_tab_options_type_file'
            )
        );

        //select
        $this->setChild('select_option_type',
            $this->getLayout()->createBlock(
                'adminhtml/catalog_product_edit_tab_options_type_select'
            )
        );

        //date
        $this->setChild('date_option_type',
            $this->getLayout()->createBlock(
                'adminhtml/catalog_product_edit_tab_options_type_date'
            )
        );

        return parent::_prepareLayout();
    }

    public function getAddButtonId()
    {
        $buttonId = $this->getLayout()
                ->getBlock('admin.product.options')
                ->getChild('add_button')->getId();
        return $buttonId;
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('delete_button');
    }

    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_type',
                'class' => 'select select-product-option-type required-option-select'
            ))
            ->setName($this->getFieldName().'[{{id}}][type]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_product_options_type')->toOptionArray());

        return $select->getHtml();
    }

    public function getRequireSelectHtml()
    {
        $select = $this->getLayout()->createBlock('adminhtml/html_select')
            ->setData(array(
                'id' => $this->getFieldId().'_{{id}}_is_require',
                'class' => 'select'
            ))
            ->setName($this->getFieldName().'[{{id}}][is_require]')
            ->setOptions(Mage::getSingleton('adminhtml/system_config_source_yesno')->toOptionArray());

        return $select->getHtml();
    }

    public function getTemplatesHtml()
    {
        $templates = $this->getChildHtml('text_option_type') . "\n" .
            $this->getChildHtml('file_option_type') . "\n" .
            $this->getChildHtml('select_option_type') . "\n" .
            $this->getChildHtml('date_option_type');

        return $templates;
    }

    public function getOptionValues()
    {
        $optionsCollection = $this->getProduct()
            ->getProductOptionsCollection()
            ->setOrder('option_id', 'asc')
//            ->setOrder('title')
            ->load();

        if (!$this->_values) {
            $values = array();
            $scope = (int) Mage::app()->getStore()->getConfig(Mage_Core_Model_Store::XML_PATH_PRICE_SCOPE);
            foreach ($optionsCollection as $option) {
                /* @var $option Mage_Catalog_Model_Product_Option */
                $value = array();

                $value['id'] = $option->getOptionId();
                $value['option_id'] = $option->getOptionId();
                $value['title'] = $option->getTitle();
                $value['type'] = $option->getType();
                $value['is_require'] = $option->getIsRequire();
                $value['sort_order'] = $option->getSortOrder();

                if ($this->getProduct()->getStoreId() != '0') {
                    $value['checkboxScopeTitle'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'title', is_null($option->getStoreTitle()));
                    $value['scopeTitleDisabled'] = is_null($option->getStoreTitle())?'disabled':null;
                }

                if ($option->getType() == 'drop_down'
                    || $option->getType() == 'checkbox'
                    || $option->getType() == 'radio'
                    || $option->getType() == 'multiple') {

                    $valueCollection = $option->getValuesCollection()
                        ->setOrder('option_type_id', 'asc')
                        ->load(false);

                    $i = 0;
                    foreach ($valueCollection as $_value) {
                        /* @var $_value Mage_Catalog_Model_Product_Option_Value */
                        $value['optionValues'][$i] = array(
                            'option_id' => $_value->getOptionId(),
                            'option_type_id' => $_value->getOptionTypeId(),
                            'title' => $_value->getTitle(),
                            'price' => $this->getPriceValue($_value->getPrice(), $_value->getPriceType()),
                            'price_type' => $_value->getPriceType(),
                            'sku' => $_value->getSku(),
                            'sort_order' => $_value->getSortOrder(),
                        );

                        if ($this->getProduct()->getStoreId() != '0') {
                            $value['optionValues'][$i]['checkboxScopeTitle'] = $this->getCheckboxScopeHtml($_value->getOptionId(), 'title', is_null($_value->getStoreTitle()), $_value->getOptionTypeId());
                            $value['optionValues'][$i]['scopeTitleDisabled'] = is_null($_value->getStoreTitle())?'disabled':null;
                            if ($scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
                                $value['optionValues'][$i]['checkboxScopePrice'] = $this->getCheckboxScopeHtml($_value->getOptionId(), 'price', is_null($_value->getstorePrice()), $_value->getOptionTypeId());
                                $value['optionValues'][$i]['scopePriceDisabled'] = is_null($_value->getStorePrice())?'disabled':null;
                            }
                        }
                        $i++;
                    }
                } else {
                    $value['price'] = $this->getPriceValue($option->getPrice(), $option->getPriceType());
                    $value['price_type'] = $option->getPriceType();
                    $value['sku'] = $option->getSku();
                    $value['max_characters'] = $option->getMaxCharacters();
                    $value['file_extension'] = $option->getFileExtension();
                    if ($this->getProduct()->getStoreId() != '0' && $scope == Mage_Core_Model_Store::PRICE_SCOPE_WEBSITE) {
                        $value['checkboxScopePrice'] = $this->getCheckboxScopeHtml($option->getOptionId(), 'price', is_null($option->getStorePrice()));
                        $value['scopePriceDisabled'] = is_null($option->getStorePrice())?'disabled':null;
                    }
                }
                $values[] = new Varien_Object($value);
            }
            $this->_values = $values;
        }

        return $this->_values;
    }

    public function getCheckboxScopeHtml($id, $name, $checked=true, $select_id='-1')
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $selectNameHtml = '';
        $selectIdHtml = '';
        if ($select_id != '-1') {
            $selectNameHtml = '[values]['.$select_id.']';
            $selectIdHtml = 'select_'.$select_id.'_';
        }
        $checkbox = '<input type="checkbox" id="'.$this->getFieldId().'_'.$id.'_'.$selectIdHtml.$name.'_use_default" class="checkbox product-option-scope-checkbox" name="'.$this->getFieldName().'['.$id.']'.$selectNameHtml.'[scope]['.$name.']" value="1" '.$checkedHtml.'/>';
        return $checkbox;
    }

    public function getPriceValue($value, $type)
    {
        if ($type == 'percent') {
            return number_format($value, 0, null, '');
        } elseif ($type == 'fixed') {
            return number_format($value, 2, null, '');
        }
    }
}