<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Magento
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magento.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test product categories resource
 *
 * @category    Magento
 * @package     Magento_Test
 * @author      Magento Api Team <api-team@magento.com>
 */

class Api2_Catalog_Products_Categories_GuestTest extends Magento_Test_Webservice_Rest_Guest
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
     * Test product categories list
     */
    public function testList()
    {
        $categoryData = require dirname(__FILE__) . '/_fixtures/Backend/ProductCategoryData.php';
        $categoryCreatedData = require dirname(__FILE__) . '/_fixtures/Backend/ProductCategoryCreatedData.php';

        $fixturesDir = realpath(dirname(__FILE__) . '/../../../../../fixtures');

        /* @var $productFixture Mage_Catalog_Model_Product */
        $product = require $fixturesDir . '/Catalog/Product.php';
        $product->setStoreId(0)
            ->setCategoryIds($categoryData['category_id'] . ',' . $categoryCreatedData['category_id'])
            ->save();
        self::setFixture('product_simple', $product);

        $restResponse = $this->callGet('products/categories/' . $product->getId());
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $restResponse->getStatus());
        $responseData = $restResponse->getBody();
        $this->assertNotEmpty($responseData);
        $originalData = $product->getCategoryIds();
        $this->assertEquals(count($responseData), count($originalData));
        $this->assertContains($categoryData['category_id'], $product->getCategoryIds());
        $this->assertContains($categoryCreatedData['category_id'], $product->getCategoryIds());
    }

    /**
     * Test product categories resource list for nonexistent product
     *
     * @magentoDataFixture Api2/Catalog/Products/Categories/_fixtures/product_simple.php
     */
    public function testListWrongProductId()
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = self::getFixture('product_simple');

        $restResponse = $this->callGet('products/categories/' . ($product->getId() + 1));
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_BAD_REQUEST, $restResponse->getStatus());
        $body = $restResponse->getBody();
        $errors = $body['messages']['error'];
        $this->assertNotEmpty($errors);

        $expectedErrors = array(
            'Product not found'
        );

        $this->assertEquals(count($expectedErrors), count($errors));
        foreach ($errors as $error) {
            $this->assertContains($error['message'], $expectedErrors);
        }
    }

    /**
     * Test product categories list
     */
    public function testListWithInactiveCategory()
    {
        $categoryData = require dirname(__FILE__) . '/_fixtures/Backend/ProductCategoryData.php';
        $categoryCreatedData = require dirname(__FILE__) . '/_fixtures/Backend/ProductCategoryCreatedData.php';

        $fixturesDir = realpath(dirname(__FILE__) . '/../../../../../fixtures');

        /* @var $productFixture Mage_Catalog_Model_Product */
        $product = require $fixturesDir . '/Catalog/Product.php';
        $product->setStoreId(0)
            ->setCategoryIds($categoryData['category_id'] . ',' . $categoryCreatedData['category_id'])
            ->save();
        self::setFixture('product_simple', $product);

        $category = self::getFixture('category');
        $category->setStoreId(0)->setData('is_active', 0)->save();

        $restResponse = $this->callGet('products/categories/' . $product->getId());

        $category->setStoreId(0)->setData('is_active', 1)->save();

        $this->assertEquals(Mage_Api2_Model_Server::HTTP_OK, $restResponse->getStatus());
        $responseData = $restResponse->getBody();
        $this->assertNotEmpty($responseData);

        $categoryIds = array();
        foreach($responseData as $categoryData) {
            $categoryIds[] = $categoryData['category_id'];
        }
        $this->assertCount(1, $categoryIds);
        $this->assertContains($categoryData['category_id'], $categoryIds);
        $this->assertNotContains($categoryCreatedData['category_id'], $categoryIds);
    }

    /**
     * Test firbidden delete for product category resource
     */
    public function testDelete()
    {
        $resourceUri = 'products/categories/1/category/1';
        $restResponse = $this->callDelete($resourceUri);
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED, $restResponse->getStatus());
    }

    /**
     * Test firbidden delete for product category resource
     */
    public function testCreate()
    {
        $restResponse = $this->callPost('products/categories');
        $this->assertEquals(Mage_Api2_Model_Server::HTTP_METHOD_NOT_ALLOWED, $restResponse->getStatus());
    }
}
