<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Layout integration tests
 *
 * Note that some methods are not covered here, see the Mage_Core_Model_LayoutDirectivesTest
 *
 * @see Mage_Core_Model_LayoutDirectivesTest
 */
class Mage_Core_Model_LayoutTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout
     */
    protected $_layout;

    protected function setUp()
    {
        $this->_layout = Mage::getModel('Mage_Core_Model_Layout');
    }

    /**
     * @param array $inputArguments
     * @param string $expectedArea
     * @dataProvider constructorDataProvider
     */
    public function testConstructor(array $inputArguments, $expectedArea)
    {
        $layout = Mage::getModel('Mage_Core_Model_Layout', $inputArguments);
        $this->assertEquals($expectedArea, $layout->getArea());
    }

    public function constructorDataProvider()
    {
        return array(
            'default area'  => array(array(), Mage_Core_Model_Design_PackageInterface::DEFAULT_AREA),
            'frontend area' => array(array('area' => 'frontend'), 'frontend'),
            'backend area'  => array(array('area' => 'adminhtml'), 'adminhtml'),
        );
    }

    public function testConstructorStructure()
    {
        $structure = new Magento_Data_Structure;
        $structure->createElement('test.container', array());
        /** @var $layout Mage_Core_Model_Layout */
        $layout = Mage::getModel('Mage_Core_Model_Layout', array('structure' => $structure));
        $this->assertTrue($layout->hasElement('test.container'));
    }

    public function testDestructor()
    {
        $this->_layout->addBlock('Mage_Core_Block_Text', 'test');
        $this->assertNotEmpty($this->_layout->getAllBlocks());
        $this->_layout->__destruct();
        $this->assertEmpty($this->_layout->getAllBlocks());
    }

    public function testGetUpdate()
    {
        $this->assertInstanceOf('Mage_Core_Model_Layout_Merge', $this->_layout->getUpdate());
    }

    public function testGetSetDirectOutput()
    {
        $this->assertFalse($this->_layout->isDirectOutput());
        $this->_layout->setDirectOutput(true);
        $this->assertTrue($this->_layout->isDirectOutput());
    }

    public function testGenerateXml()
    {
        $structure = new Magento_Data_Structure;
        /** @var $layout Mage_Core_Model_Layout */
        $layout = $this->getMock('Mage_Core_Model_Layout', array('getUpdate'), array(
            $this->getMock('Mage_Core_Model_BlockFactory', array(), array(), '', false),
            $structure,
            $this->getMock('Mage_Core_Model_Layout_Argument_Processor', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Layout_Translator', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_Layout_ScheduledStructure', array(), array(), '', false),
            $this->getMock('Mage_Core_Model_DataService_Graph', array(), array(), '', false),
        ));
        $merge = $this->getMock('StdClass', array('asSimplexml'));
        $merge->expects($this->once())->method('asSimplexml')->will($this->returnValue(simplexml_load_string(
            '<layout><container name="container1"></container></layout>',
            'Mage_Core_Model_Layout_Element'
        )));
        $layout->expects($this->once())->method('getUpdate')->will($this->returnValue($merge));
        $this->assertEmpty($layout->getXpath('/layout/container[@name="container1"]'));
        $layout->generateXml();
        $this->assertNotEmpty($layout->getXpath('/layout/container[@name="container1"]'));
    }

    /**
     * A smoke test for generating elements
     *
     * See sophisticated tests at Mage_Core_Model_LayoutDirectivesTest
     * @see Mage_Core_Model_LayoutDirectivesTest
     */
    public function testGenerateGetAllBlocks()
    {
        $this->_layout->setXml(simplexml_load_string(
            '<layout>
                <block type="Mage_Core_Block_Text" name="block1">
                    <block type="Mage_Core_Block_Text"/>
                </block>
                <block type="Mage_Core_Block_Text" template="test"/>
                <block type="Mage_Core_Block_Text"/>
            </layout>',
            'Mage_Core_Model_Layout_Element'
        ));
        $this->assertEquals(array(), $this->_layout->getAllBlocks());
        $this->_layout->generateElements();
        $expected = array('block1', 'block1_schedule_block', 'schedule_block', 'schedule_block_1');
        $this->assertSame($expected, array_keys($this->_layout->getAllBlocks()));
        $child = $this->_layout->getBlock('block1_schedule_block');
        $this->assertSame($this->_layout->getBlock('block1'), $child->getParentBlock());
        $this->assertEquals('test', $this->_layout->getBlock('schedule_block')->getData('template'));
        $this->assertFalse($this->_layout->getBlock('nonexisting'));
    }

    public function testGetElementProperty()
    {
        $name = 'test';
        $this->_layout->addContainer($name, 'Test', array('option1' => 1, 'option2' => 2));
        $this->assertEquals('Test', $this->_layout->getElementProperty(
            $name, Mage_Core_Model_Layout::CONTAINER_OPT_LABEL
        ));
        $this->assertEquals(Mage_Core_Model_Layout::TYPE_CONTAINER, $this->_layout->getElementProperty($name, 'type'));
        $this->assertSame(2, $this->_layout->getElementProperty($name, 'option2'));

        $this->_layout->addBlock('Mage_Core_Block_Text', 'text', $name);
        $this->assertEquals(Mage_Core_Model_Layout::TYPE_BLOCK, $this->_layout->getElementProperty('text', 'type'));
        $this->assertSame(array('text' => 'text'), $this->_layout->getElementProperty(
            $name, Magento_Data_Structure::CHILDREN
        ));
    }

    public function testIsBlock()
    {
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertFalse($this->_layout->isBlock('block'));
        $this->_layout->addContainer('container', 'Container');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block');
        $this->assertFalse($this->_layout->isBlock('container'));
        $this->assertTrue($this->_layout->isBlock('block'));
    }

    public function testSetUnsetBlock()
    {
        $expectedBlockName = 'block_' . __METHOD__;
        $expectedBlock = $this->_layout->createBlock('Mage_Core_Block_Text');

        $this->_layout->setBlock($expectedBlockName, $expectedBlock);
        $this->assertSame($expectedBlock, $this->_layout->getBlock($expectedBlockName));

        $this->_layout->unsetElement($expectedBlockName);
        $this->assertFalse($this->_layout->getBlock($expectedBlockName));
        $this->assertFalse($this->_layout->hasElement($expectedBlockName));
    }

    /**
     * @dataProvider createBlockDataProvider
     */
    public function testCreateBlock($blockType, $blockName, array $blockData, $expectedName)
    {
        $expectedData = $blockData + array('type' => $blockType);

        $block = $this->_layout->createBlock($blockType, $blockName, array('data' => $blockData));

        $this->assertEquals($this->_layout, $block->getLayout());
        $this->assertRegExp($expectedName, $block->getNameInLayout());
        $this->assertEquals($expectedData, $block->getData());
    }

    public function createBlockDataProvider()
    {
        return array(
            'named block' => array(
                'Mage_Core_Block_Template',
                'some_block_name_full_class',
                array('type' => 'Mage_Core_Block_Template', 'is_anonymous' => false),
                '/^some_block_name_full_class$/'
            ),
            'no name block' => array(
                'Mage_Core_Block_Text_List',
                '',
                array(
                    'type' => 'Mage_Core_Block_Text_List',
                    'key1' => 'value1',
                ),
                '/text_list/'
            ),
        );
    }

    /**
     * @dataProvider blockNotExistsDataProvider
     * @expectedException Mage_Core_Exception
     */
    public function testCreateBlockNotExists($name)
    {
        $this->_layout->createBlock($name);
    }

    public function blockNotExistsDataProvider()
    {
        return array(
            array(''),
            array('block_not_exists'),
        );
    }

    public function testAddBlock()
    {
        $this->assertInstanceOf('Mage_Core_Block_Text', $this->_layout->addBlock('Mage_Core_Block_Text', 'block1'));
        $block2 = Mage::getObjectManager()->create('Mage_Core_Block_Text');
        $block2->setNameInLayout('block2');
        $this->_layout->addBlock($block2, '', 'block1');

        $this->assertTrue($this->_layout->hasElement('block1'));
        $this->assertTrue($this->_layout->hasElement('block2'));
        $this->assertEquals('block1', $this->_layout->getParentName('block2'));
    }

    public function testAddContainer()
    {
        $this->assertFalse($this->_layout->hasElement('container'));
        $this->_layout->addContainer('container', 'Container');
        $this->assertTrue($this->_layout->hasElement('container'));
        $this->assertTrue($this->_layout->isContainer('container'));

        $this->_layout->addContainer('container1', 'Container 1', array(), 'container', 'c1');
        $this->assertEquals('container1', $this->_layout->getChildName('container', 'c1'));
    }

    public function testGetChildBlock()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block = $this->_layout->addBlock('Mage_Core_Block_Text', 'block', 'parent', 'block_alias');
        $this->_layout->addContainer('container', 'Container', array(), 'parent', 'container_alias');
        $this->assertSame($block, $this->_layout->getChildBlock('parent', 'block_alias'));
        $this->assertFalse($this->_layout->getChildBlock('parent', 'container_alias'));
    }

    /**
     * @return Mage_Core_Model_Layout
     */
    public function testSetChild()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two');
        $this->_layout->addContainer('three', 'Three');
        $this->assertSame($this->_layout, $this->_layout->setChild('one', 'two', ''));
        $this->_layout->setChild('one', 'three', '');
        $this->assertSame(array('two', 'three'), $this->_layout->getChildNames('one'));
        return $this->_layout;
    }

    /**
     * @param Mage_Core_Model_Layout $layout
     * @depends testSetChild
     */
    public function testReorderChild(Mage_Core_Model_Layout $layout)
    {
        $layout->addContainer('four', 'Four', array(), 'one');

        // offset +1
        $layout->reorderChild('one', 'four', 1);
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));

        // offset -2
        $layout->reorderChild('one', 'three', 2, false);
        $this->assertSame(array('two', 'three', 'four'), $layout->getChildNames('one'));

        // after sibling
        $layout->reorderChild('one', 'two', 'three');
        $this->assertSame(array('three', 'two', 'four'), $layout->getChildNames('one'));

        // after everyone
        $layout->reorderChild('one', 'three', '-');
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));

        // before sibling
        $layout->reorderChild('one', 'four', 'two', false);
        $this->assertSame(array('four', 'two', 'three'), $layout->getChildNames('one'));

        // before everyone
        $layout->reorderChild('one', 'two', '-', false);
        $this->assertSame(array('two', 'four', 'three'), $layout->getChildNames('one'));
    }

    public function testGetChildBlocks()
    {
        $this->_layout->addContainer('parent', 'Parent');
        $block1 = $this->_layout->addBlock('Mage_Core_Block_Text', 'block1', 'parent');
        $this->_layout->addContainer('container', 'Container', array(), 'parent');
        $block2 = $this->_layout->addBlock('Mage_Core_Block_Template', 'block2', 'parent');
        $this->assertSame(array('block1' => $block1, 'block2' => $block2), $this->_layout->getChildBlocks('parent'));
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testAddBlockInvalidType()
    {
        $this->_layout->addBlock('invalid_name', 'child');
    }

    public function testIsContainer()
    {
        $block = 'block';
        $container = 'container';
        $this->_layout->addBlock('Mage_Core_Block_Text', $block);
        $this->_layout->addContainer($container, 'Container');
        $this->assertFalse($this->_layout->isContainer($block));
        $this->assertTrue($this->_layout->isContainer($container));
        $this->assertFalse($this->_layout->isContainer('invalid_name'));
    }

    public function testIsManipulationAllowed()
    {
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block1');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block2', 'block1');
        $this->assertFalse($this->_layout->isManipulationAllowed('block1'));
        $this->assertFalse($this->_layout->isManipulationAllowed('block2'));

        $this->_layout->addContainer('container1', 'Container 1');
        $this->_layout->addBlock('Mage_Core_Block_Text', 'block3', 'container1');
        $this->_layout->addContainer('container2', 'Container 2', array(), 'container1');
        $this->assertFalse($this->_layout->isManipulationAllowed('container1'));
        $this->assertTrue($this->_layout->isManipulationAllowed('block3'));
        $this->assertTrue($this->_layout->isManipulationAllowed('container2'));
    }

    public function testRenameElement()
    {
        $blockName = 'block';
        $expBlockName = 'block_renamed';
        $containerName = 'container';
        $expContainerName = 'container_renamed';
        $block = $this->_layout->createBlock('Mage_Core_Block_Text', $blockName);
        $this->_layout->addContainer($containerName, 'Container');

        $this->assertEquals($block, $this->_layout->getBlock($blockName));
        $this->_layout->renameElement($blockName, $expBlockName);
        $this->assertEquals($block, $this->_layout->getBlock($expBlockName));

        $this->_layout->hasElement($containerName);
        $this->_layout->renameElement($containerName, $expContainerName);
        $this->_layout->hasElement($expContainerName);
    }

    public function testGetBlock()
    {
        $this->assertFalse($this->_layout->getBlock('test'));
        $block = Mage::app()->getLayout()->createBlock('Mage_Core_Block_Text');
        $this->_layout->setBlock('test', $block);
        $this->assertSame($block, $this->_layout->getBlock('test'));
    }

    public function testGetParentName()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'Two', array(), 'one');
        $this->assertFalse($this->_layout->getParentName('one'));
        $this->assertEquals('one', $this->_layout->getParentName('two'));
    }

    public function testGetElementAlias()
    {
        $this->_layout->addContainer('one', 'One');
        $this->_layout->addContainer('two', 'One', array(), 'one', '1');
        $this->assertFalse($this->_layout->getElementAlias('one'));
        $this->assertEquals('1', $this->_layout->getElementAlias('two'));
    }

    /**
     * @covers Mage_Core_Model_Layout::addOutputElement
     * @covers Mage_Core_Model_Layout::getOutput
     * @covers Mage_Core_Model_Layout::removeOutputElement
     */
    public function testGetOutput()
    {
        $blockName = 'block_' . __METHOD__;
        $expectedText = "some_text_for_$blockName";

        $block = $this->_layout->addBlock('Mage_Core_Block_Text', $blockName);
        $block->setText($expectedText);

        $this->_layout->addOutputElement($blockName);
        // add the same element twice should not produce output duplicate
        $this->_layout->addOutputElement($blockName);
        $this->assertEquals($expectedText, $this->_layout->getOutput());

        $this->_layout->removeOutputElement($blockName);
        $this->assertEmpty($this->_layout->getOutput());
    }

    public function testGetMessagesBlock()
    {
        $this->assertInstanceOf('Mage_Core_Block_Messages', $this->_layout->getMessagesBlock());
    }

    public function testGetBlockSingleton()
    {
        $block = $this->_layout->getBlockSingleton('Mage_Core_Block_Text');
        $this->assertInstanceOf('Mage_Core_Block_Text', $block);
        $this->assertSame($block, $this->_layout->getBlockSingleton('Mage_Core_Block_Text'));
    }

    public function testHelper()
    {
        $helper = $this->_layout->helper('Mage_Core_Helper_Data');
        $this->assertInstanceOf('Mage_Core_Helper_Data', $helper);
        $this->assertSame($this->_layout, $helper->getLayout());
    }

    /**
     * @dataProvider findTranslationModuleNameDefaultsDataProvider
     */
    public function testFindTranslationModuleNameDefaults($node, $moduleName)
    {
        $this->markTestIncomplete('Method it self not finished as has commented out logic.');
        $this->assertEquals($moduleName, Mage_Core_Model_Layout::findTranslationModuleName($node));
    }

    /**
     * @return array
     */
    public function findTranslationModuleNameDefaultsDataProvider()
    {
        $layout = '<layout>
            <catalogsearch_test>
                <block type="test/test">
                    <block type="child/test"></block>
                </block>
            </catalogsearch_test>
        </layout>';
        $layout = simplexml_load_string($layout, 'Varien_Simplexml_Element');
        $block = $layout->xpath('catalogsearch_test/block/block');
        $block = $block[0];
        return array(
            array(
                simplexml_load_string('<node module="Notexisting_Module">test</node>', 'Varien_Simplexml_Element'),
                'core'
            ),
            array($block, 'core'),
        );
    }
}
