<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\AbandonedCarts;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertAbandonedCartCustomerInfoResult
 * Assert customer info in Abandoned Carts report
 */
class AssertAbandonedCartCustomerInfoResult extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert customer info in Abandoned Carts report (Reports > Abandoned carts):
     * – name and email
     * – products and qty
     * – created and updated date
     *
     * @param AbandonedCarts $abandonedCarts
     * @param array $products
     * @param CustomerInjectable $customer
     * @return void
     */
    public function processAssert(AbandonedCarts $abandonedCarts, $products, CustomerInjectable $customer)
    {
        $abandonedCarts->open();
        $qty = 0;
        foreach ($products as $product) {
            $qty += $product->getCheckoutData()['qty'];
        }
        $filter = [
            'customer_name' => $customer->getFirstname() . " " . $customer->getLastname(),
            'email' => $customer->getEmail(),
            'items_count' => count($products),
            'items_qty' => $qty,
            'created_at' => date('m/j/Y'),
            'updated_at' => date('m/j/Y'),
        ];
        $abandonedCarts->getGridBlock()->search($filter);
        $filter['created_at'] = date('M j, Y');
        $filter['updated_at'] = date('M j, Y');
        \PHPUnit_Framework_Assert::assertTrue(
            $abandonedCarts->getGridBlock()->isRowVisible($filter, false, false),
            'Expected customer info is absent in Abandoned Carts report grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer info in Abandoned Carts report grid is correct.';
    }
}
