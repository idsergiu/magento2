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
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Delete Widget Test
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_CmsWidgets_DeleteTest extends Mage_Selenium_TestCase
{
    public function setUpBeforeTests()
    {
        $this->loginAdminUser();
    }

    protected function assertPreconditions()
    {
        $this->addParameter('id', '0');
    }

    /**
     * <p>Preconditions</p>
     * <p>Creates Category to use during tests</p>
     * @test
     * @return string
     */
    public function createCategory()
    {
        //Data
        $categoryData = $this->loadData('sub_category_required');
        //Steps
        $this->navigate('manage_categories', false);
        $this->categoryHelper()->checkCategoriesPage();
        $this->categoryHelper()->createCategory($categoryData);
        //Verification
        $this->assertMessagePresent('success', 'success_saved_category');
        $this->categoryHelper()->checkCategoriesPage();

        return $categoryData['parent_category'] . '/' . $categoryData['name'];
    }

    /**
     * Create required products for testing
     * @depends createCategory
     * @test
     *
     * @param $category
     */
    public function createProducts($category)
    {
        $products = array();
        $productTypes = array('simple');
        $this->navigate('manage_products');
        //Data
        foreach ($productTypes as $productType) {
            $productData = $this->loadData($productType . '_product_required', array('categories' => $category),
                                               array('general_name', 'general_sku'));
            //Steps
            $this->productHelper()->createProduct($productData, $productType);
            //Verifying
            $this->assertMessagePresent('success', 'success_saved_product');
            $products['sku'][$productType] = $productData['general_sku'];
            $products['name'][$productType] = $productData['general_name'];
        }
        return $products;
    }

    /**
     * <p>Creates All Types of widgets with required fields only and delete them</p>
     * <p>Steps:</p>
     * <p>1. Navigate to Manage Widgets page</p>
     * <p>2. Create all types of widgets with required fields filled</p>
     * <p>3. Open newly created widget</p>
     * <p>4. Delete opened widget</p>
     * <p>Expected result</p>
     * <p>Widgets are created and deleted successfully</p>
     *
     * @param array $dataWidgetType
     * @param string $category
     * @param array $products
     *
     * @test
     * @dataProvider widgetTypesReqDataProvider
     * @depends createCategory
     * @depends createProducts
     * @TestlinkId	TL-MAGE-3232
     */
    public function deleteAllTypesOfWidgets($dataWidgetType, $category, $products)
    {
        $this->navigate('manage_cms_widgets');
        $temp = array();
        $temp['filter_sku'] = $products['sku']['simple'];
        $temp['category_path'] = $category;
        $widgetData = $this->loadData($dataWidgetType . '_widget_req', $temp, 'widget_instance_title');
        $this->cmsWidgetsHelper()->createWidget($widgetData);
        $widgetToDelete = array('filter_type'  => $widgetData['settings']['type'],
                                'filter_title' => $widgetData['frontend_properties']['widget_instance_title']);
        $this->cmsWidgetsHelper()->deleteWidget($widgetToDelete);
    }

    public function widgetTypesReqDataProvider()
    {
        return array(
            array('cms_page_link'),
            array('cms_static_block'),
            array('catalog_category_link'),
            array('catalog_new_products_list'),
            array('catalog_product_link'),
            array('orders_and_returns'),
            array('recently_compared_products'),
            array('recently_viewed_products')
        );
    }
}