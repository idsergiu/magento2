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
 * Test product images resource
 *
 * @category    Magento
 * @package     Magento_Test
 * @author      Magento Api Team <api-team@magento.com>
 */

class Webapi_Catalog_Product_Image_GuestTest extends Magento_Test_Webservice_Rest_Guest
{
    /**
     * Delete fixtures
     */
    protected function tearDown()
    {
        self::deleteFixture('product_simple', true);
        parent::tearDown();
    }

    /**
     * Test list images
     *
     * @magentoDataFixture testsuite/Webapi/Catalog/Product/Image/_fixture/product_simple.php
     * @resourceOperation product_image::multiget
     */
    public function testList()
    {
        $imagesNumber = 3;
        $pathPrefix = '/p/r/';
        /* @var $product Mage_Catalog_Model_Product */
        $product = self::getFixture('product_simple');

        $ioAdapter = new Varien_Io_File();
        $fileNames = array();
        $fileFixtures = array();
        for ($i=0; $i<$imagesNumber; $i++) {
            $fileNames[$i] = 'product_image' . uniqid() . '.jpg';
            $fileFixtures[$i] = dirname(__FILE__) . '/_fixture/' . $fileNames[$i];
            $ioAdapter->cp(TEST_FIXTURE_DIR . '/_data/Catalog/Product/product.jpg', $fileFixtures[$i]);
            $exclude = false;
            if ($i == 0) {
                // customer should not see excluded image
                $exclude = true;
            }
            $product->addImageToMediaGallery($fileFixtures[$i], null, false, $exclude);
        }
        $product->save();
        foreach ($fileFixtures as $fileFixture) {
            $ioAdapter->rm($fileFixture);
        }

        $restResponse = $this->callGet('products/' . $product->getId() . '/images');
        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $restResponse->getStatus());
        $body = $restResponse->getBody();
        foreach ($fileNames as $index => $fileName) {
            $found = false;
            foreach ($body as $imageData) {
                if (isset($imageData['url']) && strstr($imageData['url'], $pathPrefix . $fileName)) {
                    $found = true;
                    break;
                }
            }
            if ($index == 0) {
                $this->assertFalse($found, 'Image ' . $pathPrefix . $fileName . ' excluded and user should not see it');
            } else {
                $this->assertTrue($found, 'Image ' . $pathPrefix . $fileName . ' not attached');
            }
        }
    }

    /**
     * Test image get
     *
     * @magentoDataFixture testsuite/Webapi/Catalog/Product/Image/_fixture/product_simple.php
     * @resourceOperation product_image::get
     */
    public function testGet()
    {
        $imageData = require dirname(__FILE__) . '/_fixture/_data/image_data.php';
        $imageData = $imageData['data_set_1'];

        $pathPrefix = '/p/r/';
        /* @var $product Mage_Catalog_Model_Product */
        $product = self::getFixture('product_simple');

        $ioAdapter = new Varien_Io_File();
        $fileName = 'product_image' . uniqid() . '.jpg';
        $fileFixture = dirname(__FILE__) . '/_fixture/' . $fileName;
        $ioAdapter->cp(TEST_FIXTURE_DIR . '/_data/Catalog/Product/product.jpg', $fileFixture);
        $product->addImageToMediaGallery($fileFixture, null, false, false);

        $attributes = $product->getTypeInstance()->getSetAttributes($product);
        $this->assertTrue(isset($attributes['media_gallery']));
        $gallery = $attributes['media_gallery'];
        /* @var $gallery Mage_Catalog_Model_Resource_Eav_Attribute */
        $gallery->getBackend()->updateImage($product, $pathPrefix . $fileName, $imageData);
        $gallery->getBackend()->setMediaAttribute($product, $imageData['types'], $pathPrefix . $fileName);
        $product->setStoreId(0)->save();

        $ioAdapter->rm($fileFixture);

        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product')->setStoreId(0)->load($product->getId());
        self::setFixture('product_simple', $product);

        $gallery = $product->getData('media_gallery');
        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($gallery['images'][0]['value_id']));
        $imageId = $gallery['images'][0]['value_id'];

        $restResponse = $this->callGet('products/' . $product->getId() . '/images/' . $imageId);
        $body = $restResponse->getBody();
        $this->assertEquals(Mage_Webapi_Controller_Front_Rest::HTTP_OK, $restResponse->getStatus());

        $this->assertTrue(isset($body['label']));
        $this->assertTrue(isset($body['position']));
        $this->assertTrue(isset($body['url']));
        $this->assertTrue(isset($body['types']));
        $this->assertContains($pathPrefix . $fileName, $body['url']);
        $this->assertEquals($imageData['label'], $body['label']);
        $this->assertEquals($imageData['position'], $body['position']);

        $types = array();
        foreach ($product->getMediaAttributes() as $attribute) {
            if ($product->getData($attribute->getAttributeCode()) == $gallery['images'][0]['file']) {
                $types[] = $attribute->getAttributeCode();
            }
        }
        foreach ($body['types'] as $type) {
            $found = false;
            foreach ($types as $realType) {
                if ($type == $realType) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }

    /**
     * Test image get for excluded image
     *
     * @magentoDataFixture testsuite/Webapi/Catalog/Product/Image/_fixture/product_simple.php
     * @resourceOperation product_image::get
     */
    public function testGetExcluded()
    {
        $pathPrefix = '/p/r/';
        /* @var $product Mage_Catalog_Model_Product */
        $product = self::getFixture('product_simple');

        $ioAdapter = new Varien_Io_File();
        $fileName = 'product_image' . uniqid() . '.jpg';
        $fileFixture = dirname(__FILE__) . '/_fixture/' . $fileName;
        $ioAdapter->cp(TEST_FIXTURE_DIR . '/_data/Catalog/Product/product.jpg', $fileFixture);
        // add excluded image
        $product->addImageToMediaGallery($fileFixture, null, false, true);
        $product->setStoreId(0)->save();
        $ioAdapter->rm($fileFixture);

        /* @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('Mage_Catalog_Model_Product')->setStoreId(0)->load($product->getId());
        self::setFixture('product_simple', $product);

        $gallery = $product->getData('media_gallery');
        $this->assertNotEmpty($gallery);
        $this->assertTrue(isset($gallery['images'][0]['value_id']));
        $imageId = $gallery['images'][0]['value_id'];

        $restResponse = $this->callGet('products/' . $product->getId() . '/images/' . $imageId);
        $this->assertEquals(Mage_Webapi_Exception::HTTP_NOT_FOUND, $restResponse->getStatus());
    }
}
