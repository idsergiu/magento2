<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */

class Mage_Core_Model_Layout_File_List_FactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Layout_File_List_Factory
     */
    private $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = $this->getMockForAbstractClass('Magento_ObjectManager');
        $this->_model = new Mage_Core_Model_Layout_File_List_Factory($this->_objectManager);
    }

    public function testCreate()
    {
        $list = new Mage_Core_Model_Layout_File_List();
        $this->_objectManager
            ->expects($this->once())
            ->method('create')
            ->with('Mage_Core_Model_Layout_File_List')
            ->will($this->returnValue($list))
        ;
        $this->assertSame($list, $this->_model->create());
    }
}
