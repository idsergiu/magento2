<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Backend_Block_System_Config_Form_FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_System_Config_Form_Field
     */
    protected $_object;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_elementMock;

    /**
     * @var array
     */
    protected $_testData;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appModelMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperFactoryMock;

    protected function setUp()
    {
        $this->_appModelMock = $this->getMock('Mage_Core_Model_App', array(), array(), '', false, false);
        $this->_helperFactoryMock = $this->getMock('Mage_Core_Model_Factory_Helper',
            array(), array(), '', false, false
        );

        $data = array(
            'applicationModel' => $this->_appModelMock,
            'helperFactory' => $this->_helperFactoryMock,
        );
        $this->_object = new Mage_Backend_Block_System_Config_Form_Field($data);

        $this->_testData = array(
            'htmlId' => 'test_field_id',
            'name' => 'test_name',
            'label' => 'test_label',
            'elementHTML' => 'test_html',
        );

        $this->_elementMock = $this->getMock('Varien_Data_Form_Element_Text',
            array('getHtmlId' , 'getName', 'getLabel', 'getElementHtml', 'getComment', 'getHint', 'getScope',
                'getScopeLabel', 'getInherit', 'getCanUseWebsiteValue', 'getCanUseDefaultValue', 'setDisabled'
            ),
            array(),
            '',
            false,
            false,
            true
        );

        $this->_elementMock->expects($this->any())->method('getHtmlId')
            ->will($this->returnValue($this->_testData['htmlId']));
        $this->_elementMock->expects($this->any())->method('getName')
            ->will($this->returnValue($this->_testData['name']));
        $this->_elementMock->expects($this->any())->method('getLabel')
            ->will($this->returnValue($this->_testData['label']));
        $this->_elementMock->expects($this->any())->method('getElementHtml')
            ->will($this->returnValue($this->_testData['elementHTML']));
    }

    public function testRenderHtmlIdLabelInputElementName()
    {
        $expected = '<tr id="row_' . $this->_testData['htmlId'] . '">';
        $expected .= '<td class="label"><label for="' . $this->_testData['htmlId'] . '">'
            . $this->_testData['label'] . '</label></td>';
        $expected .= '<td class="value">' . $this->_testData['elementHTML'] . '</td>';
        $expected .= '<td class="scope-label"></td>';
        $expected .= '<td class=""></td></tr>';

        $actual = $this->_object->render($this->_elementMock);

        $this->assertEquals($expected, $actual);
    }

    public function testRenderValueWithCommentBlock()
    {
        $testComment = 'test_comment';
        $this->_elementMock->expects($this->any())->method('getComment')->will($this->returnValue($testComment));
        $expected = '<td class="value">' . $this->_testData['elementHTML']
            . '<p class="note"><span>' . $testComment . '</span></p></td>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertContains($expected, $actual);
    }

    public function testRenderHint()
    {
        $testHint = 'test_hint';
        $this->_elementMock->expects($this->any())->method('getHint')->will($this->returnValue($testHint));
        $expected = '<td class=""><div class="hint"><div style="display: none;">' . $testHint . '</div></div>';
        $actual = $this->_object->render($this->_elementMock);
        $this->assertContains($expected, $actual);
    }

    public function testRenderScopeLabel()
    {
        $this->_appModelMock->expects($this->once())->method('isSingleStoreMode')->will($this->returnValue(false));

        $testScopeLabel = 'test_scope_label';
        $this->_elementMock->expects($this->any())->method('getScope')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getScopeLabel')->will($this->returnValue($testScopeLabel));

        $expected = '<td class="scope-label">' . $testScopeLabel . '</td>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertContains($expected, $actual);
    }

    public function testRenderInheritCheckbox()
    {
        $this->_elementMock->expects($this->any())->method('getInherit')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getCanUseWebsiteValue')->will($this->returnValue(true));
        $this->_elementMock->expects($this->any())->method('getCanUseDefaultValue')->will($this->returnValue(true));
        $this->_elementMock->expects($this->once())->method('setDisabled')->with(true);

        $helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false, false);
        $helperMock->expects($this->any())->method('__')->will($this->returnArgument(0));

        $this->_helperFactoryMock->expects($this->any())->method('get')
            ->with('Mage_Backend_Helper_Data')->will($this->returnValue($helperMock));

        $expected = '<td class="use-default">';
        $expected .= '<input id="' . $this->_testData['htmlId'] . '_inherit" name="'
            . $this->_testData['name'] . '[inherit]" type="checkbox" value="1"'
            . ' class="checkbox config-inherit" checked="checked"'
            . ' onclick="toggleValueElements(this, Element.previous(this.parentNode))" /> ';

        $expected .= '<label for="' . $this->_testData['htmlId'] . '_inherit" class="inherit">Use Website</label>';
        $actual = $this->_object->render($this->_elementMock);

        $this->assertContains($expected, $actual);
    }
}
