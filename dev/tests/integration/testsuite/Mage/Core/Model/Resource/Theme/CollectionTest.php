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

class Mage_Core_Model_Resource_Theme_CollectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    protected static function _getThemesCollection()
    {
        return  Mage::getObjectManager()->create('Mage_Core_Model_Resource_Theme_Collection');
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCollection()
    {
        Mage::getConfig();
        $oldTotalRecords = self::_getThemesCollection()->getSize();

        $collection = $this->setThemeFixture();
        $oldThemes = $collection->toArray();

        $newThemeCollection = self::_getThemesCollection();
        $newThemes = $newThemeCollection->toArray();

        $expectedTotalRecords = $oldTotalRecords + count(self::getThemeList());
        $this->assertEquals($expectedTotalRecords, $newThemes['totalRecords']);
        $this->assertEquals($oldThemes['items'], $newThemes['items']);
    }

    /**
     * @param string $fullPath
     * @param bool $shouldExist
     * @magentoDataFixture setThemeFixture
     * @dataProvider getThemeByFullPathDataProvider
     */
    public function testGetThemeByFullPath($fullPath, $shouldExist)
    {
        $themeCollection = self::_getThemesCollection();
        $hasFound = false;
        /** @var $theme Mage_Core_Model_Theme */
        foreach ($themeCollection as $theme) {
            if ($theme->getFullPath() == $fullPath) {
                $hasFound = true;
                break;
            }
        }
        $message = $shouldExist ? 'Theme not found' : 'Theme is found but it should not' ;
        $this->assertEquals($shouldExist, $hasFound, $message);
    }

    /**
     * @magentoDataFixture setThemeFixture
     * @magentoDbIsolation enabled
     */
    public function testAddAreaFilter()
    {
        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = Mage::getObjectManager()->create('Mage_Core_Model_Resource_Theme_Collection');
        $themeCollection->addAreaFilter('test_area');
        $this->assertEquals(1, count($themeCollection));

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = Mage::getObjectManager()->create('Mage_Core_Model_Resource_Theme_Collection');
        $themeCollection->addAreaFilter('test_area2');
        $this->assertEquals(1, count($themeCollection));

        /** @var $themeCollection Mage_Core_Model_Resource_Theme_Collection */
        $themeCollection = Mage::getObjectManager()->create('Mage_Core_Model_Resource_Theme_Collection');
        $themeCollection->addAreaFilter('test_area3');
        $this->assertEquals(0, count($themeCollection));
    }

    /**
     * @return array
     */
    public function getThemeByFullPathDataProvider()
    {
        return array(
            array('test_area/test/default', true),
            array('test_area2/test/pro', true),
            array('test_area/test/pro', false),
            array('test_area2/test/default', false),
            array('', false),
            array('test_area', false),
            array('test_area/test', false),
            array('test_area/test/something', false),
        );
    }

    /**
     * @magentoDataFixture setInheritedThemeFixture
     */
    public function testCheckParentInThemes()
    {
        $collection = self::_getThemesCollection(); //->checkParentInThemes();
        foreach (self::getInheritedThemeList() as $themeData) {
            $fullPath = $themeData['area'] . '/' . $themeData['theme_path'];
            $parentIdActual = $collection->clear()->getThemeByFullPath($fullPath)->getParentId();
            if ($themeData['parent_id']) {
                $parentFullPath = trim($themeData['parent_id'], '{}');
                $parentIdExpected = (int)$collection->clear()->getThemeByFullPath($parentFullPath)->getId();
                $this->assertEquals(
                    $parentIdActual,
                    $parentIdExpected,
                    sprintf('Invalid parent_id for theme "%s"', $fullPath)
                );
            } else {
                $parentIdExpected = 0;
                $this->assertEquals(
                    $parentIdExpected,
                    $parentIdActual,
                    sprintf('Parent id should be null for "%s"', $fullPath)
                );
            }
        }
    }

    /**
     * Set themes fixtures
     *
     * @return Mage_Core_Model_Resource_Theme_Collection
     */
    public static function setThemeFixture()
    {
        $themeCollection = self::_getThemesCollection();
        $themeCollection->load();
        foreach (self::getThemeList() as $themeData) {
            /** @var $themeModel Mage_Core_Model_Theme */
            $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
            $themeModel->setData($themeData);
            $themeCollection->addItem($themeModel);
        }
        return $themeCollection->save();
    }

    /**
     * @throws Exception
     */
    public static function setInheritedThemeFixture()
    {
        $fixture = self::getInheritedThemeList();
        $idByPath = array();
        foreach ($fixture as $themeData) {
            /** @var $themeModel Mage_Core_Model_Theme */
            $themeModel = Mage::getObjectManager()->create('Mage_Core_Model_Theme');
            $themeModel->setData($themeData);

            //if ($themeModel->getFullPath() == 'test1/test1')

            if ($themeData['parent_id'] && isset($idByPath[$themeData['parent_id']])) {
                $themeModel->setParentId($idByPath[$themeData['parent_id']]);
            }
            $themeModel->save();

            $idByPath[$themeModel->getFullPath()] = $themeModel->getId();
        }
    }

    /**
     * Get themes for making fixture
     *
     * @return array
     */
    public static function getThemeList()
    {
        return array(
            array(
                'parent_id'            => '0',
                'theme_path'           => 'test/default',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Test',
                'preview_image'        => 'test_default.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'test_area'
            ),
            array(
                'parent_id'            => '0',
                'theme_path'           => 'test/pro',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Professional Test',
                'preview_image'        => 'test_default.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'test_area2'
            ),
        );
    }

    /**
     * @return array
     */
    public static function getInheritedThemeList()
    {
        return array(
            array(
                'parent_id'            => '0',
                'theme_path'           => 'test1/test1',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Test1',
                'preview_image'        => 'test1_test1.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'area51'
            ),
            array(
                'parent_id'            => 'area51/test1/test1',
                'theme_path'           => 'test1/test2',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Test2',
                'preview_image'        => 'test1_test2.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'area51'
            ),
            array(
                'parent_id'            => 'area51/test1/test2',
                'theme_path'           => 'test1/test3',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Test3',
                'preview_image'        => 'test1_test3.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'area51'
            ),
            array(
                'parent_id'            => 'area51/test1/test0',
                'theme_path'           => 'test1/test4',
                'theme_version'        => '2.0.0.0',
                'theme_title'          => 'Test4',
                'preview_image'        => 'test1_test4.jpg',
                'magento_version_from' => '2.0.0.0',
                'magento_version_to'   => '*',
                'is_featured'          => '1',
                'area'                 => 'area51'
            ),
        );
    }
}
