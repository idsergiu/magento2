<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductCompare;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductCompareSuccessRemoveMessage
 * Assert message is appeared on "Compare Products" block on myAccount page
 */
class AssertProductCompareSuccessRemoveMessage extends AbstractConstraint
{
    const SUCCESS_MESSAGE = 'You removed product %s from the comparison list.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert message is appeared on "Compare Products" block on myAccount page
     *
     * @param CatalogProductCompare $catalogProductCompare
     * @param FixtureInterface $product
     * @return void
     */
    public function processAssert(CatalogProductCompare $catalogProductCompare, FixtureInterface $product)
    {
        $successMessage = sprintf(self::SUCCESS_MESSAGE, $product->getName());
        $actualMessage = $catalogProductCompare->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals($successMessage, $actualMessage, 'Wrong success message is displayed.');
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product has been removed from compare products list.';
    }
}
