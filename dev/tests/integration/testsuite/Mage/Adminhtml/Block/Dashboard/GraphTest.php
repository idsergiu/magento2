<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @group module:Mage_Adminhtml
 */
class Mage_Adminhtml_Block_Dashboard_GraphTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Adminhtml_Block_Dashboard_Graph
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = new Mage_Adminhtml_Block_Dashboard_Graph;
        $this->_block->setDataHelperName('Mage_Adminhtml_Helper_Dashboard_Order');
    }

    public function testGetChartUrl()
    {
        $this->assertStringStartsWith('http://chart.apis.google.com/chart', $this->_block->getChartUrl());
    }
}
