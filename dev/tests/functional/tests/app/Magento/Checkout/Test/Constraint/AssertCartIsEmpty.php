<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Checkout\Test\Fixture\Cart;
use Magento\Checkout\Test\Page\CheckoutCart;
use Mtf\Client\Browser;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCartIsEmpty
 * Check that Shopping Cart is empty
 */
class AssertCartIsEmpty extends AbstractConstraint
{
    /**
     * Text of empty cart.
     */
    const TEXT_EMPTY_CART = 'You have no items in your shopping cart. Click here to continue shopping.';

    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'middle';

    /**
     * Check that Shopping Cart is empty, opened page contains text "You have no items in your shopping cart.
     * Click here to continue shopping." where 'here' is link that leads to index page
     *
     * @param CheckoutCart $checkoutCart
     * @param Browser $browser
     * @return void
     */
    public function processAssert(CheckoutCart $checkoutCart, Browser $browser)
    {
        $checkoutCart->open();
        $cartEmptyBlock = $checkoutCart->getCartEmptyBlock();

        \PHPUnit_Framework_Assert::assertEquals(
            self::TEXT_EMPTY_CART,
            $cartEmptyBlock->getText(),
            'Wrong text on empty cart page.'
        );

        $cartEmptyBlock->clickLinkToMainPage();
        \PHPUnit_Framework_Assert::assertEquals(
            $_ENV['app_frontend_url'],
            $browser->getUrl(),
            'Wrong link to main page on empty cart page.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Shopping Cart is empty.';
    }
}
