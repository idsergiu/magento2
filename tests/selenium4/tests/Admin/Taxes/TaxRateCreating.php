<?php

class Admin_Taxes_TaxRateCreating extends TestCaseAbstract {

    /**
     * Setup procedure.
     * Initializes model and loads configuration
     */
    function setUp() {
        $this->model = $this->getModel('admin/tax');
        $this->setUiNamespace();
    }

    /**
     * Test creating Tax Rate
     */
    function testCreateTaxRate() {
        $taxData = array(
        'product_tax_class_name' => Core::getEnvConfig('backend/tax/product_tax_class_name'),
        'customer_tax_class_name' => Core::getEnvConfig('backend/tax/customer_tax_class_name'),
        'tax_rate_identifier' => Core::getEnvConfig('backend/tax/tax_rate_identifier/tzr1'),
        'tax_rate_percent' => Core::getEnvConfig('backend/tax/tax_rate_percent'),
        'tax_rule_name' => Core::getEnvConfig('backend/tax/tax_rule_name'),
        'zip_post_code' => Core::getEnvConfig('backend/tax/zip_post_code'),
        'zip_is_range' => 'Yes',
        'zip_range_from' => '1',
        'zip_range_to' => '2',
        'country' => Core::getEnvConfig('backend/tax/country'),
        'state' => Core::getEnvConfig('backend/tax/state'),
        'tax_rule_priority' => Core::getEnvConfig('backend/tax/tax_rule_priority'),
        'tax_rule_sort_order' => Core::getEnvConfig('backend/tax/tax_rule_sort_order'),
        'store_view_name' => '',//array('Default Store View','SmokeTestStoreView'),//Core::getEnvConfig('backend/scope/store_view/name'),
        'tax_store_view_title' => Core::getEnvConfig('backend/tax/tax_store_view_title'),
            );
        if ($this->model->doLogin()) {
            $this->model->createTaxRate($taxData);
        }
    }

}