<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Launcher
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @magentoDataFixture Mage/Launcher/_files/pages.php
 */
class Mage_Launcher_Model_TileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Launcher_Model_Tile
     */
    protected $_tile;

    protected function setUp()
    {
        $this->_tile = Mage::getModel('Mage_Launcher_Model_Tile');
    }

    public function testLoadByCode()
    {
        $this->_tile->loadByCode('tile_1');
        $this->assertEquals('tile_1', $this->_tile->getCode());
    }

    public function testIsSkippableFlagSetByDatabaseByDefault()
    {
        // tile_1 was saved by fixture without specifying is_skippable property
        $this->assertFalse($this->_tile->isSkippable());
        $this->_tile->loadByCode('tile_1');
        $this->assertTrue($this->_tile->isSkippable());
    }

    public function testIsDismissibleFlagSetByDatabaseByDefault()
    {
        // tile_1 was saved by fixture without specifying is_dismissible property
        $this->assertFalse($this->_tile->isDismissible());
        $this->_tile->loadByCode('tile_1');
        $this->assertTrue($this->_tile->isDismissible());
    }

    /**
     * @expectedException Zend_Db_Statement_Exception
     */
    public function testSaveCannotPersistTwoTilesWithTheSameCode()
    {
        // tile tile_1 has been already created by fixture
        $tile = Mage::getModel('Mage_Launcher_Model_Tile');
        $tile->setCode('tile_1')
            ->save();
    }
}
