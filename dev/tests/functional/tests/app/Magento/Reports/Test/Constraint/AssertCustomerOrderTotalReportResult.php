<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Reports\Test\Page\Adminhtml\CustomerTotalsReport;

/**
 * Class AssertCustomerOrderTotalReportResult
 * Assert OrderTotalReport grid for all params
 */
class AssertCustomerOrderTotalReportResult extends AbstractAssertCustomerOrderReportResult
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert OrderTotalReport grid for all params
     *
     * @param CustomerTotalsReport $customerTotalsReport
     * @param CustomerInjectable $customer
     * @param array $columns
     * @param array $report
     * @return void
     */
    public function processAssert(
        CustomerTotalsReport $customerTotalsReport,
        CustomerInjectable $customer,
        array $columns,
        array $report
    ) {
        $filter = $this->prepareFilter($customer, $columns, $report);

        \PHPUnit_Framework_Assert::assertTrue(
            $customerTotalsReport->getGridBlock()->isRowVisible($filter),
            'Order does not present in report grid.'
        );
    }

    /**
     * Returns a string representation of successful assertion
     *
     * @return string
     */
    public function toString()
    {
        return 'Order total is present in reports grid.';
    }
}
