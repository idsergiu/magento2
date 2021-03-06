<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAbsenceInAddAttributeSearch
 * Checks that product attribute cannot be added to product template on Product Page via Add Attribute control
 */
class AssertProductAttributeAbsenceInSearchOnProductForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that deleted attribute can't be added to product template on Product Page via Add Attribute control
     *
     * @param CatalogProductAttribute $productAttribute
     * @param CatalogProductIndex $productGrid
     * @param CatalogProductNew $newProductPage
     * @return void
     */
    public function processAssert(
        CatalogProductAttribute $productAttribute,
        CatalogProductIndex $productGrid,
        CatalogProductNew $newProductPage
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        \PHPUnit_Framework_Assert::assertFalse(
            $newProductPage->getProductForm()->checkAttributeInSearchAttributeForm($productAttribute),
            "Product attribute found in Attribute Search form."
        );
    }

    /**
     * Text absent Product Attribute in Attribute Search form
     *
     * @return string
     */
    public function toString()
    {
        return "Product Attribute is absent in Attribute Search form.";
    }
}
