<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     performance_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

return array(
    'install_options' => array(
        'license_agreement_accepted' => 'yes',
        'locale'                     => 'en_US',
        'timezone'                   => 'America/Los_Angeles',
        'default_currency'           => 'USD',
        'db_host'                    => 'localhost',
        'db_name'                    => 'bamboo_performance',
        'db_user'                    => 'root',
        'db_pass'                    => '',
        'use_secure'                 => 'no',
        'use_secure_admin'           => 'no',
        'use_rewrites'               => 'no',
        'admin_lastname'             => 'Admin',
        'admin_firstname'            => 'Admin',
        'admin_email'                => 'admin@example.com',
        'admin_username'             => 'admin',
        'admin_password'             => '123123q',
    ),
    'scenarios' => 'testsuite/{home_page,checkout}.jmx',
    'fixtures' => 'testsuite/fixtures/*.php',
    'scenario_params' => array(
        'host'  => 'mage2.magentocommerce.com',
        'path'  => '/bamboo-agent/',
        'users' => 10,
        'loops' => 100,
    ),
    'report_dir' => 'report',
);
