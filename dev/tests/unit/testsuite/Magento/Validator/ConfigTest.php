<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Framework
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Magento_Validator_ConfigTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Magento_Validator_Config
     */
    protected static $_model = null;

    public static function setUpBeforeClass()
    {
        self::$_model = new Magento_Validator_Config(glob(__DIR__ . '/_files/validation/positive/*/validation.xml'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructException()
    {
        new Magento_Validator_Config(array());
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetValidationRulesInvalidEntityName()
    {
        self::$_model->getValidationRules('invalid_entity', null);
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetValidationRulesInvalidGroupName()
    {
        self::$_model->getValidationRules('test_entity', 'invalid_group');
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetValidationRulesInvalidZendConstraint()
    {
        $configFile = glob(__DIR__ . '/_files/validation/negative/invalid_zend_constraint.xml');
        $config = new Magento_Validator_Config($configFile);
        $config->getValidationRules('test_entity', 'test_group_a');
    }

    /**
     * @expectedException Magento_Exception
     */
    public function testGetValidationRulesInvalidMagentoConstraint()
    {
        $configFile = glob(__DIR__ . '/_files/validation/negative/invalid_magento_constraint.xml');
        $config = new Magento_Validator_Config($configFile);
        $config->getValidationRules('test_entity', 'test_group_a');
    }

    /**
     * @dataProvider getValidationRulesDataProvider
     * @param string $entityName
     * @param string $groupName
     * @param array $expectedRules
     */
    public function testGetValidationRules($entityName, $groupName, $expectedRules)
    {
        $actualRules = self::$_model->getValidationRules($entityName, $groupName);
        $this->assertRulesEqual($expectedRules, $actualRules);
    }

    /**
     * Assert that all expected validation rules are present with correct constraint objects.
     *
     * @param array $expectedRules
     * @param array $actualRules
     */
    public function assertRulesEqual(array $expectedRules, array $actualRules)
    {
        foreach ($expectedRules as $expectedRule => $expectedConstraints) {
            $this->assertArrayHasKey($expectedRule, $actualRules);

            foreach ($expectedConstraints as $expectedConstraint) {
                $constraintFound = false;
                foreach ($actualRules[$expectedRule] as $actualConstraint) {
                    if ($expectedConstraint['constraint'] instanceof $actualConstraint['constraint']) {
                        $constraintFound = true;
                        if (isset($expectedConstraint['field'])) {
                            $this->assertArrayHasKey('field', $actualConstraint);
                            $this->assertEquals($expectedConstraint['field'], $actualConstraint['field']);
                        }
                        break;
                    }
                }
                if (!$constraintFound) {
                    $this->fail(sprintf('Expected constraint "%s" was not found in the rule "%"',
                        get_class($expectedConstraint['constraint']), $expectedRule));
                }
            }
        }
    }

    public function getValidationRulesDataProvider()
    {
        $groupARules = array(
            'test_rule_zend' => array(
                array(
                    'constraint' => $this->getMock('Zend_Validate_Alnum'),
                    'field' => 'test_field'
                ),
            ),
            'test_rule_constraint' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                ),
            ),
        );
        $groupBRules = array(
            'test_rule_constraint' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                ),
            ),
            'test_rule_constraint_2' => array(
                array(
                    'constraint' => $this->getMock('Magento_Validator_Test'),
                    'field' => 'constraint_field'
                ),
            ),
        );
        $groupCRules = array(
            'test_rule' => array(
                array(
                    'constraint' => $this->getMock('Zend_Validate_Int'),
                    'field' => 'test_field'
                ),
            ),
        );

        return array(
            array('test_entity', 'test_group_a', $groupARules),
            array('test_entity', 'test_group_b', $groupBRules),
            array('test_entity_b', 'test_group_c', $groupCRules),
        );
    }

    public function testGetSchemaFile()
    {
        $this->assertFileExists(self::$_model->getSchemaFile());
    }
}

/** Dummy classes to test interface validation. */
class Magento_Validator_Invalid_Interface {
}
class Magento_Validator_Test implements Magento_Validator_ConstraintInterface
{
    public function isValidData(array $data, $field = null) {
        return true;
    }
    public function getErrors() {
        return array();
    }
}
