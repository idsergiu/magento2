<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Validator
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Test case for Magento_Validator_ValidatorAbstract
 */
class Magento_Validator_ValidatorAbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var null|Magento_Translate_AdapterInterface
     */
    protected $_defaultTranslator = null;

    protected function setUp()
    {
        $this->_defaultTranslator = Magento_Validator_ValidatorAbstract::getDefaultTranslator();
    }

    protected function tearDown()
    {
        Magento_Validator_ValidatorAbstract::setDefaultTranslator($this->_defaultTranslator);
    }

    /**
     * Get translator object
     *
     * @return PHPUnit_Framework_MockObject_MockObject|Magento_Translate_AdapterAbstract
     */
    protected function _getTranslator()
    {
        return $this->getMockBuilder('Magento_Translate_AdapterInterface')
            ->getMockForAbstractClass();
    }

    /**
     * Test default translator get/set
     */
    public function testDefaultTranslatorGetSet()
    {
        $translator = $this->_getTranslator();
        Magento_Validator_ValidatorAbstract::setDefaultTranslator($translator);
        $this->assertEquals($translator, Magento_Validator_ValidatorAbstract::getDefaultTranslator());
    }

    /**
     * Test get/set/has translator
     */
    public function testTranslatorGetSetHas()
    {
        /** @var Magento_Validator_ValidatorAbstract $validator */
        $validator = $this->getMockBuilder('Magento_Validator_ValidatorAbstract')
            ->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        $validator->setTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertTrue($validator->hasTranslator());
    }

    /**
     * Check that default translator returned if set and no translator set
     */
    public function testGetTranslatorDefault()
    {
        /** @var Magento_Validator_ValidatorAbstract $validator */
        $validator = $this->getMockBuilder('Magento_Validator_ValidatorAbstract')
            ->getMockForAbstractClass();
        $translator = $this->_getTranslator();
        Magento_Validator_ValidatorAbstract::setDefaultTranslator($translator);
        $this->assertEquals($translator, $validator->getTranslator());
        $this->assertFalse($validator->hasTranslator());
    }
}
