<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     Mage_Review
 * @subpackage  functional_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Helper class
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Review_Helper extends Mage_Selenium_TestCase
{
    /**
     * Creates review
     *
     * @param array|string $reviewData
     */
    public function createReview($reviewData)
    {
        if (is_string($reviewData)) {
            $elements = explode('/', $reviewData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $reviewData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $this->clickButton('add_new_review');
        $product = (isset($reviewData['product_to_review'])) ? $reviewData['product_to_review'] : array();
        if (!$product) {
            $this->fail('Data for selecting product for review is not set');
        }
        $this->searchAndOpen($product, false, 'select_product_grid');
        $this->validatePage();
        $this->fillInfo($reviewData);
        $this->saveForm('save_review');
    }

    /**
     * Edit existing review
     *
     * @param array $reviewData
     * @param array $searchData
     */
    public function editReview(array $reviewData, array $searchData)
    {
        $this->openReview($searchData);
        $this->fillInfo($reviewData);
        $this->saveForm('save_review');
    }

    /**
     * Opens review
     *
     * @param array $reviewSearch
     */
    public function openReview(array $reviewSearch)
    {
        if (isset($reviewSearch['filter_websites']) && !$this->controlIsPresent('dropdown', 'filter_websites')) {
            unset($reviewSearch['filter_websites']);
        }
        $this->searchAndOpen($reviewSearch);
    }

    /**
     * Search reviews
     *
     * @param array $reviewSearch
     */
    public function fillSearchFormAndOpenReview(array $reviewSearch)
    {
        if (isset($reviewSearch['filter_websites']) && !$this->controlIsPresent('dropdown', 'filter_websites')) {
            unset($reviewSearch['filter_websites']);
        }

        $waitAjax = true;
        $xpath = '';
        $xpathContainer = null;
        $resetXpath = $this->_getControlXpath('button', 'reset_filter', $xpathContainer);
        $jsName = $this->getAttribute($resetXpath . '@onclick');
        $jsName = preg_replace('/\.[\D]+\(\)/', '', $jsName);

        $scriptXpath = "//script[contains(text(),\"$jsName.useAjax = ''\")]";
        if ($this->isElementPresent($scriptXpath)) {
            $waitAjax = false;
        }

        // Click 'Reset' button
        $this->click($resetXpath);
        if ($waitAjax) {
            $this->waitForAjax();
        } else {
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        }
        $qtyElementsInTable = $this->_getControlXpath('pageelement', 'qtyElementsInTable');

        //Forming xpath that contains string 'Total $number records found' where $number - number of items in table
        $totalCount = intval($this->getText($xpath . $qtyElementsInTable));
        $xpathPager = $xpath . $qtyElementsInTable . "[not(text()='" . $totalCount . "')]";

        $xpathTR = $this->formSearchXpath($reviewSearch);

        // Fill in search form and click 'Search' button
        $this->fillForm($reviewSearch);
        $this->clickButton('search', false);
        $this->waitForElement($xpathPager);

        $xpathTR = $xpath . $xpathTR;
        if ($this->isElementPresent($xpathTR)) {
            $itemId = $this->defineIdFromTitle($xpathTR);
            $this->addParameter('id', $itemId);
            $this->click($xpathTR . "/td[contains(text(),'" . $reviewSearch[array_rand($reviewSearch)] . "')]");
            $this->waitForPageToLoad($this->_browserTimeoutPeriod);
            $this->validatePage();
        } else {
            $this->fail('Can\'t find item in grid for data: ' . print_r($reviewSearch, true));
        }
    }

    /**
     * Fills tabs in new/edit review
     *
     * @param string|array $reviewData
     */
    public function fillInfo($reviewData)
    {
        if (isset($reviewData['visible_in']) && !$this->controlIsPresent('multiselect', 'visible_in')) {
            unset($reviewData['visible_in']);
        }
        $this->fillForm($reviewData);
        if (isset($reviewData['product_rating'])) {
            $this->fillRatings($reviewData['product_rating']);
        }
    }

    /**
     * Fills ratings
     *
     * @param array $detailedRatings
     */
    public function fillRatings(array $detailedRatings)
    {
        if ($this->controlIsPresent('message', 'not_available_rating')) {
            $this->fail('Rating is not available for this store view');
        }
        foreach ($detailedRatings as $value) {
            if (isset($value['rating_name']) && isset($value['stars'])) {
                $this->addParameter('ratingName', $value['rating_name']);
                $this->addParameter('stars', $value['stars']);
                $this->fillRadiobutton('detailed_rating', 'yes');
            } else {
                $this->fail('Incorrect data to fill');
            }
        }
    }

    /**
     * Open Review and delete
     *
     * @param array $searchData
     */
    public function deleteReview(array $searchData)
    {
        $this->openReview($searchData);
        $this->clickButtonAndConfirm('delete_review', 'confirmation_for_delete');
    }

    /**
     * Verify Review
     *
     * @param array|string $reviewData
     * @param array $skipFields
     */
    public function verifyReviewData($reviewData, $skipFields = array())
    {
        if (is_string($reviewData)) {
            $elements = explode('/', $reviewData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $reviewData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        if (isset($reviewData['visible_in']) && !$this->controlIsPresent('multiselect', 'visible_in')) {
            $skipFields = array_merge($skipFields, array('visible_in'));
        }
        $ratings = (isset($reviewData['product_rating'])) ? $reviewData['product_rating'] : array();

        $this->verifyForm($reviewData, $skipFields);
        foreach ($ratings as $ratingData) {
            $this->addParameter('ratingName', $ratingData['rating_name']);
            $this->addParameter('stars', $ratingData['stars']);
            $this->verifyChecked($this->_getControlXpath('radiobutton', 'detailed_rating'));
        }
        $this->assertEmptyVerificationErrors();
    }

    #********************************************
    #           Frontend Methods                *
    #********************************************

    /**
     * <p>Create Review</p>
     *
     * @param array|string $reviewData
     * @param bool $validateRating      In case $validateRating == TRUE - rating filling will be mandatory
     */
    public function frontendAddReview($reviewData, $validateRating = true)
    {
        if (is_string($reviewData)) {
            $elements = explode('/', $reviewData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $reviewData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        $linkName = ($this->controlIsPresent('link', 'add_your_review')) ? 'add_your_review' : 'first_review';
        $this->defineCorrectParam($linkName);
        $this->clickControl('link', $linkName);
        $this->fillForm($reviewData);
        if (isset($reviewData['ratings'])) {
            $this->frontendAddRating($reviewData['ratings'], $validateRating);
        }
        $this->saveForm('submit_review');
    }

    /**
     * Filling In Rating
     *
     * @param array|string $ratingData
     * @param bool $validateRating
     */
    public function frontendAddRating($ratingData, $validateRating = true)
    {
        if (is_string($ratingData)) {
            $elements = explode('/', $ratingData);
            $fileName = (count($elements) > 1) ? array_shift($elements) : '';
            $ratingData = $this->loadDataSet($fileName, implode('/', $elements));
        }
        foreach ($ratingData as $value) {
            $this->addParameter('rateName', $value['rating_name']);
            $this->addParameter('rateId', $value['stars']);
            if ($this->controlIsPresent('radiobutton', 'select_rate')) {
                $this->fillRadiobutton('select_rate', 'Yes');
            } else {
                $this->addVerificationMessage('Rating with name ' . $value['rating_name'] . ' is not on the page');
            }
        }
        if ($validateRating) {
            $this->assertEmptyVerificationErrors();
        }
    }

    /**
     * Review verification after approve
     * (@TODO doesn't work for several reviews posted by one nickname)
     *
     * @param array $verifyData
     * @param string $productName
     */
    public function frontVerifyReviewDisplaying(array $verifyData, $productName)
    {
        $this->addParameter('productName', $productName);

        $review = (isset($verifyData['review'])) ? $verifyData['review'] : '';
        $nickname = (isset($verifyData['nickname'])) ? $verifyData['nickname'] : '';
        $summary = (isset($verifyData['summary_of_review'])) ? $verifyData['summary_of_review'] : '';
        $rating = (isset($verifyData['product_rating'])) ? $verifyData['product_rating'] : array();
        $ratingNames = array();
        $actualRatings = array();
        foreach ($rating as $value) {
            $ratingNames[] = $value['rating_name'];
        }
        if ($this->controlIsPresent('link', 'reviews')) {
            //Open reviews
            $this->defineCorrectParam('reviews');
            $this->clickControl('link', 'reviews');
            $this->addParameter('reviewerName', $nickname);
            if (!$this->controlIsPresent('pageelement', 'review_reviewer_name')) {
                $this->fail('Customer with nickname \'' . $nickname . '\' does not added approved review');
            }
            //Define actual review summary
            $actualSummary = $this->getText($this->_getControlXpath('link', 'review_summary'));
            //Define actual review text and rating names
            $xpathReview = $this->_getControlXpath('pageelement', 'review_details');
            $xpathReviewDate = $this->_getControlXpath('pageelement', 'review_post_date');
            $xpathReviewRatings = $xpathReview . '/table';
            $text = preg_quote($this->getText($xpathReviewDate));
            $actualReview = trim(preg_replace('#' . $text . '#', '', $this->getText($xpathReview)));
            if ($this->isElementPresent($xpathReviewRatings)) {
                $text = preg_quote($this->getText($xpathReviewRatings));
                $actualReview = trim(preg_replace('#' . $text . '#', '', $actualReview), " \t\n\r\0\x0B");
                $ratingsCount = $this->getXpathCount($xpathReviewRatings . '//th');
                for ($i = 0; $i < $ratingsCount; $i++) {
                    $actualRatings[] = $this->getTable($xpathReviewRatings . '.' . $i . '.0');
                }
            }
            //Verification on product page
            $this->assertEquals($summary, $actualSummary,
                'Review Summary is not equal to specified: (' . $summary . ' != ' . $actualSummary . ')');
            $this->assertEquals($review, $actualReview,
                'Review Text is not equal to specified: (' . $review . ' != ' . $actualReview . ')');
            $this->assertEquals($ratingNames, $actualRatings, 'Review Rating names is not equal to specified');
            //Verification on Review Details page
            $this->clickControl('link', 'review_summary');
            $this->verifyTextPresent($productName, $productName . ' product not display on Review Details page');
            $this->verifyTextPresent($review, '\'' . $review . '\' review text not display on Review Details page');
            $this->assertEmptyVerificationErrors();
        } else {
            $this->fail('Product does not have approved review(s)');
        }
    }

    /**
     * Verification review on frontend
     * (@TODO doesn't work for several reviews posted by one nickname)
     *
     * @param array $reviewData
     * @param string $productName
     */
    public function frontVerifyReviewDisplayingInMyAccount($reviewData, $productName)
    {
        //Verification in "My Account"
        $this->navigate('customer_account');
        $this->addParameter('productName', $productName);
        $this->assertTrue($this->controlIsPresent('link', 'product_name'),
            "Can not find product with name: $productName in My Recent Reviews block");
        $this->clickControl('link', 'product_name');
        $this->assertTextPresent($reviewData['review'],
            '\'' . $reviewData['review'] . '\' review text not display on Review Details page');
        //Verification in "My Account -> My Product Reviews"
        $this->navigate('my_product_reviews');
        $this->assertTrue($this->controlIsPresent('link', 'product_name'),
            "Can not find product with name: $productName in My Product Reviews block");
    }

    /**
     * Add parameter ReviewId
     *
     * @param string $linkName
     */
    public function defineCorrectParam($linkName)
    {
        $url = $this->getAttribute($this->_getControlXpath('link', $linkName) . "/@href");
        $this->addParameter('categoryId', $this->defineParameterFromUrl('category', $url));
    }
}
