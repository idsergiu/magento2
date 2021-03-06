<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CatalogSearch\Test\Constraint;

use Magento\CatalogSearch\Test\Fixture\CatalogSearchQuery;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchEdit;
use Magento\CatalogSearch\Test\Page\Adminhtml\CatalogSearchIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertSearchTermForm
 * Assert that after save a search term on edit term search page displays
 */
class AssertSearchTermForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that after save a search term on edit term search page displays:
     *  - correct Search Query field passed from fixture
     *  - correct Store
     *  - correct Number of results
     *  - correct Number of Uses
     *  - correct Synonym For
     *  - correct Redirect URL
     *  - correct Display in Suggested Terms
     *
     * @param CatalogSearchIndex $indexPage
     * @param CatalogSearchEdit $editPage
     * @param CatalogSearchQuery $searchTerm
     * @return void
     */
    public function processAssert(
        CatalogSearchIndex $indexPage,
        CatalogSearchEdit $editPage,
        CatalogSearchQuery $searchTerm
    ) {
        $indexPage->open()->getGrid()->searchAndOpen(['search_query' => $searchTerm->getQueryText()]);
        $formData = $editPage->getForm()->getData($searchTerm);
        $fixtureData = $searchTerm->getData();

        \PHPUnit_Framework_Assert::assertEquals(
            $formData,
            $fixtureData,
            'This form "Search Term" does not match the fixture data.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'These form "Search Term" correspond to the fixture data.';
    }
}
