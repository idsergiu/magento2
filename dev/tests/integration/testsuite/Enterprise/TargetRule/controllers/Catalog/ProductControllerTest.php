<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Enterprise_TargetRule
 * @subpackage  integration_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * @group module:Enterprise_TargetRule
 */
class Enterprise_TargetRule_Catalog_ProductControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @covers Enterprise/TargetRule/view/frontend/catalog/product/list/related.html in scope of MAGETWO-774
     * @magentoDataFixture Mage/Catalog/controllers/_files/products.php
     * @magentoDataFixture Enterprise/TargetRule/_files/related.php
     */
    public function testProductViewActionRelated()
    {
        $this->dispatch('catalog/product/view/id/1');
        $content = $this->getResponse()->getBody();
        $this->assertContains('Related Products', $content);
        $this->assertContains('Simple Product 2 Name', $content);
    }

    /**
     * @covers Enterprise/TargetRule/view/frontend/catalog/product/list/upsell.html in scope of MAGETWO-774
     * @magentoDataFixture Mage/Catalog/controllers/_files/products.php
     * @magentoDataFixture Enterprise/TargetRule/_files/upsell.php
     */
    public function testProductViewActionUpsell()
    {
        $this->dispatch('catalog/product/view/id/1');
        $content = $this->getResponse()->getBody();
        $this->assertContains('You may also be interested in the following product(s)', $content);
        $this->assertContains('Simple Product 2 Name', $content);
    }
}
