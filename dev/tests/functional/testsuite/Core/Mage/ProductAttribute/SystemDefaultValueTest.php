<?php
/**
 * Magento
 *
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ProductAttribute
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */
/**
 * Check the possibility to set default value to system attributes with dropdown type
 */
class Core_Mage_ProductAttribute_SystemDefaultValueTest extends Mage_Selenium_TestCase
{
    /**
     * Preconditions:
     * Navigate to System - Manage Attributes.
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
    }

    /**
     * Default value for System attributes
     * Preconditions:
     * 1. System attribute is existed.
     * 2. System attribute is presented in Default attribute set.
     *
     * @param string $attributeCode
     * @param string $productType
     * @param string $uimapName
     *
     * @test
     * @dataProvider systemAttributeDataProvider
     * @TestlinkId TL-MAGE-5749, TL-MAGE-5750, TL-MAGE-5751, TL-MAGE-5752, TL-MAGE-5753, TL-MAGE-5754,
     *             TL-MAGE-5755, TL-MAGE-5756, TL-MAGE-5757, TL-MAGE-5758, TL-MAGE-5759, TL-MAGE-5760,
     *             TL-MAGE-5761, TL-MAGE-5762, TL-MAGE-5835, TL-MAGE-5836
     */
    public function checkDefaultValue($attributeCode, $productType, $uimapName)
    {
        //Data
        $attributeData = $this->loadDataSet('SystemAttributes', $attributeCode);
        $productData = $this->loadDataSet('Product', $productType . '_product_required');
        unset($productData[$uimapName]);
        $searchData = $this->loadDataSet('ProductAttribute', 'attribute_search_data',
            array('attribute_code' => $attributeData['attribute_code']));
        if ($attributeCode == 'status') {
            $searchData['attribute_label'] = 'Status';
        }
        //Steps
        $this->productAttributeHelper()->openAttribute($searchData);
        //Verifying
        $this->productAttributeHelper()->verifySystemAttribute($attributeData);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $isSelected = $this->getControlAttribute('checkbox', 'default_value_by_option_name', 'selectedValue');
        $this->assertTrue($isSelected,
            'Option with value "' . $attributeData['default_value'] . '" is not set as default for attribute');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, $productType);
        $this->assertMessagePresent('success', 'success_saved_product');
        $this->productHelper()->openProduct(array('product_sku' => $productData['general_sku']));
        //Verifying
        if ($attributeCode == 'custom_design') {
            $this->productHelper()->openProductTab('design');
            $this->assertEquals($attributeData['default_control_value'],
                $this->getControlAttribute('dropdown', $uimapName, 'selectedLabel'),
                'Incorrect default value for custom design attribute.');
        } else {
            $productData[$uimapName] = $attributeData['default_value'];
        }
        $this->productHelper()->verifyProductInfo($productData, array('product_attribute_set'));
    }

    /**
     * DataProvider with system attributes list
     *
     * @return array
     */
    public function systemAttributeDataProvider()
    {
        return array(
            array('country_of_manufacture', 'simple', 'autosettings_country_manufacture'),
            array('custom_design', 'simple', 'design_custom_design'),
            array('enable_googlecheckout', 'simple', 'prices_enable_googlecheckout'),
            array('gift_message_available', 'simple', 'autosettings_allow_gift_message'),
            array('is_recurring', 'simple', 'prices_enable_recurring_profile'),
            array('msrp_enabled', 'simple', 'prices_apply_map'),
            array('msrp_display_actual_price_type', 'simple', 'prices_display_actual_price'),
            array('options_container', 'simple', 'design_display_product_options_in'),
            array('page_layout', 'simple', 'design_page_layout'),
            array('price_view', 'bundle', 'general_price_view_bundle'),
            array('status', 'simple', 'product_online_status'),
            array('tax_class_id', 'simple', 'general_tax_class'),
            array('visibility', 'simple', 'autosettings_visibility')
        );
    }

    /**
     * Change selected default value for tax_class_id to '-- Please Select --'
     * and verify impossibility to save product in this case
     *
     * @test
     * @TestlinkId TL-MAGE-6082
     */
    public function resetDefaultValue()
    {
        //Data
        $attribute = $this->loadDataSet('SystemAttributes', 'tax_class_id',
            array('default_value' => '-- Please Select --'));
        $productData = $this->loadDataSet('Product', 'simple_product_required',
            array('general_tax_class' => '%noValue%'));
        //Preconditions
        $this->productAttributeHelper()->openAttribute(array('attribute_code' => $attribute['attribute_code']));
        $this->productAttributeHelper()->processAttributeValue($attribute, false, true);
        $this->saveAndContinueEdit('button', 'save_and_continue_edit');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $isSelected = $this->getControlAttribute('checkbox', 'default_value_by_option_name', 'selectedValue');
        $this->assertTrue($isSelected,
            'Option with value "' . $attribute['default_value'] . '" is not set as default for attribute');
        //Steps
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($productData, 'simple', false);
        //Verifying
        $this->assertTrue($this->controlIsVisible('button', 'save_disabled'));
//        $this->addFieldIdToMessage('dropdown', 'general_tax_class');
//        $this->assertMessagePresent('validation', 'empty_required_field');
//        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }
}
