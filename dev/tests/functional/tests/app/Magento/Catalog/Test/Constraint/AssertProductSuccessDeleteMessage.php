<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductSuccessDeleteMessage
 */
class AssertProductSuccessDeleteMessage extends AbstractConstraint
{
    /**
     * Text value to be checked
     */
    const SUCCESS_DELETE_MESSAGE = 'A total of %d record(s) have been deleted.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that after deleting product success message.
     *
     * @param FixtureInterface|FixtureInterface[] $product
     * @param CatalogProductIndex $productPage
     * @return void
     */
    public function processAssert($product, CatalogProductIndex $productPage)
    {
        $products = is_array($product) ? $product : [$product];
        $deleteMessage = sprintf(self::SUCCESS_DELETE_MESSAGE, count($products));
        $actualMessage = $productPage->getMessagesBlock()->getSuccessMessages();
        \PHPUnit_Framework_Assert::assertEquals(
            $deleteMessage,
            $actualMessage,
            'Wrong success message is displayed.'
            . "\nExpected: " . $deleteMessage
            . "\nActual: " . $actualMessage
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Assertion that products success delete message is present.';
    }
}
