<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_ProductAttribute
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Create new product attribute. Type: Text Field
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_ProductAttribute_CodeGenerationTest extends Mage_Selenium_TestCase
{
    /**
     * Preconditions:
     * Navigate to System -> Manage Attributes.
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('manage_attributes');
    }

    /**
     * Checking of generation attribute code from attribute label
     *
     * @param $label
     * @param $attributeCode
     *
     * @test
     * @dataProvider attributeCodeGenerationDataProvider
     * @TestlinkId TL-MAGETWO-14
     */
    public function verifyGeneratedValue($label, $attributeCode)
    {
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_textfield',
            array(
                 'attribute_code' => '%noValue%',
                 'attribute_label' => $label
            )
        );
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->productAttributeHelper()->openAttribute(array('attribute_code' => $attributeCode));
        //Verifying
        $attrData['advanced_properties']['attribute_code']= $attributeCode;
        $this->productAttributeHelper()->verifyAttribute($attrData);
    }

    public function attributeCodeGenerationDataProvider()
    {
        $index = $this->generate('string', 5, ':lower:');
        $number = $this->generate('string', 5, ':digit:');
        $punct = str_replace(array('@', '&'), '', $this->generate('string', 30, ':punct:'));

        return array(
            array('Size' . $index, 'size' . $index),
            array('Size UK' . $index, 'size_uk' . $index),
            array('Skład' . $index, 'sklad'. $index),
            array('Размер' . $index, 'razmer' . $index),
            array($number, 'attr_' . $number),
            array('@&™©' . $index, 'at_tmc'. $index),
            array($punct . $index, $index),
        );
    }

    /**
     * Checking of generation attribute code from attribute label with invalid length value
     *
     * @return array
     *
     * @test
     * @TestlinkId TL-MAGETWO-14
     */
    public function verifyGeneratedLongValue()
    {
        //Data
        $validValue = $this->generate('string', 30, ':lower:');
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_textfield',
            array(
                 'attribute_code' => '%noValue%',
                 'attribute_label' => $validValue . $this->generate('string', 5, ':digit:')
            )
        );
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->productAttributeHelper()->openAttribute(array('attribute_code' => $validValue));
        //Verifying
        $attrData['advanced_properties']['attribute_code']= $validValue;
        $this->productAttributeHelper()->verifyAttribute($attrData);

        return $validValue;
    }

    /**
     * Checking generated from attribute label attribute code which already exist
     *
     * @test
     * @depends verifyGeneratedLongValue
     * @TestlinkId TL-MAGETWO-15
     */
    public function verifyExistValue($validValue)
    {
        $this->markTestIncomplete('MAGETWO-8909');
        //Data
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_textfield',
            array(
                 'attribute_code' => '%noValue%',
                 'attribute_label' => $validValue)
        );
        //Steps
        $this->productAttributeHelper()->createAttribute($attrData);
        //Verifying
        $this->assertMessagePresent('error', 'exists_attribute_code');
    }

    /**
     * Checking of generation attribute code from attribute label if label contains only unsupported characters
     *
     * @test
     * @TestlinkId TL-MAGETWO-16
     */
    public function verifyInvalidValue()
    {
        //Data
        $invalidValue = str_replace(array('@', '&'), '', $this->generate('string', 30, ':punct:'));
        $attrData = $this->loadDataSet('ProductAttribute', 'product_attribute_textfield',
            array(
                 'attribute_code' => '%noValue%',
                 'attribute_label' => $invalidValue)
        );
        //Steps
        $this->clickButton('add_new_attribute');
        $this->productAttributeHelper()->fillAttributeTabs($attrData);
        $this->saveAndContinueEdit('button','save_and_continue_edit');
        //Verifying
        $this->assertMessagePresent('success', 'success_saved_attribute');
        $this->openTab('properties');
        if (!$this->isControlExpanded(self::FIELD_TYPE_PAGEELEMENT, 'advanced_attribute_properties_section')) {
            $this->clickControl(self::FIELD_TYPE_PAGEELEMENT, 'advanced_attribute_properties_section', false);
        }
        $generatedCode = $this->getControlAttribute('field', 'attribute_code', 'value');
        $this->assertStringStartsWith('attr_', $generatedCode, 'Attribute code is not generated according ro rules');
    }
}
