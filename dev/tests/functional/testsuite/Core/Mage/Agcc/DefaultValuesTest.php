<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Agcc
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Auto Generated Specific Coupon Codes checking default values in Coupons Information form (SCPR)
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Agcc_DefaultValuesTest extends Mage_Selenium_TestCase
{
    public function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('system_configuration');
        $this->systemConfigurationHelper()->openConfigurationTab('customers_promotions');
    }

    /**
     * @test
     * @TestlinkId TL-MAGE-3900
     * @author yaroslav.goncharuk
     */
    public function codeLengthDefaultValue()
    {
        //Steps
        $defaultValue = '10';
        $this->fillField('code_length', $defaultValue);
        $this->clickButton('save_config');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_config');
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('Agcc', 'scpr_required_fields_with_agcc');
        $this->agccHelper()->createRuleAndContinueEdit($ruleData);
        $this->openTab('manage_coupon_codes');
        $this->addParameter('defaultValue', $defaultValue);
        $xpath = $this->_getControlXpath('pageelement', 'default_code_length');
        //Verification
        if (!$this->elementIsPresent($xpath)) {
            $this->fail('Wrong specified default value in Code Length field');
        }
    }

    /**
     * @test
     * @TestlinkId TL-MAGE-3934
     * @author yaroslav.goncharuk
     */
    public function codeFormatDefaultValue()
    {
        //Steps
        $defaultValue = 'num';
        $this->fillDropdown('code_format', $defaultValue);
        $this->clickButton('save_config');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_config');
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('Agcc', 'scpr_required_fields_with_agcc');
        $this->agccHelper()->createRuleAndContinueEdit($ruleData);
        $this->openTab('manage_coupon_codes');
        $this->addParameter('defaultValue', $defaultValue);
        $xpath = $this->_getControlXpath('pageelement', 'default_code_format');
        //Verification
        if (!$this->elementIsPresent($xpath)) {
            $this->fail('Wrong specified default value in Code Format dropdown');
        }
    }

    /**
     * @test
     * @param string $defaultValue
     * @dataProvider codeSuffixDefaultValueDataProvider
     * @TestlinkId TL-MAGE-3935
     * @author yaroslav.goncharuk
     */
    public function codePrefixDefaultValue($defaultValue)
    {
        //Steps
        $this->fillField('code_prefix', $defaultValue);
        $this->clickButton('save_config');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_config');
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('Agcc', 'scpr_required_fields_with_agcc');
        $this->agccHelper()->createRuleAndContinueEdit($ruleData);
        $this->openTab('manage_coupon_codes');
        $this->addParameter('defaultValue', $defaultValue);
        $xpath = $this->_getControlXpath('pageelement', 'default_code_prefix');
        //Verification
        if (!$this->elementIsPresent($xpath)) {
            $this->fail('Wrong specified default value in Code Prefix field');
        }
    }

    /**
     * @test
     * @param string $defaultValue
     * @dataProvider codeSuffixDefaultValueDataProvider
     * @TestlinkId TL-MAGE-3936
     * @author yaroslav.goncharuk
     */
    public function codeSuffixDefaultValue($defaultValue)
    {
        //Steps
        $this->fillField('code_suffix', $defaultValue);
        $this->clickButton('save_config');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_config');
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('Agcc', 'scpr_required_fields_with_agcc');
        $this->agccHelper()->createRuleAndContinueEdit($ruleData);
        $this->openTab('manage_coupon_codes');
        $this->addParameter('defaultValue', $defaultValue);
        $xpath = $this->_getControlXpath('pageelement', 'default_code_suffix');
        //Verification
        if (!$this->elementIsPresent($xpath)) {
            $this->fail('Wrong specified default value in Code Suffix field');
        }
    }

    public function codeSuffixDefaultValueDataProvider()
    {
        return array(
            array($this->generate('string', 5, ':alnum:')),
            array($this->generate('string', 5, ':alpha:')),
            array($this->generate('string', 5, ':digit:')),
            array($this->generate('string', 5, ':lower:')),
            array($this->generate('string', 5, ':upper:')),
            array($this->generate('string', 5, ':punct:')),
        );
    }

    /**
     * @test
     * @TestlinkId TL-MAGE-3937
     * @author yaroslav.goncharuk
     */
    public function dashDefaultValue()
    {
        //Steps
        $defaultValue = '3';
        $this->fillField('dash_every_x_characters', $defaultValue);
        $this->clickButton('save_config');
        //Verification
        $this->assertMessagePresent('success', 'success_saved_config');
        //Steps
        $this->navigate('manage_shopping_cart_price_rules');
        $ruleData = $this->loadDataSet('Agcc', 'scpr_required_fields_with_agcc');
        $this->agccHelper()->createRuleAndContinueEdit($ruleData);
        $this->openTab('manage_coupon_codes');
        $this->addParameter('defaultValue', $defaultValue);
        $xpath = $this->_getControlXpath('pageelement', 'default_dash_every_x_characters');
        //Verification
        if (!$this->elementIsPresent($xpath)) {
            $this->fail('Wrong specified default value in Dash Every X Characters field');
        }
    }
}