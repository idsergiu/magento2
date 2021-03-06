<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

return [
    'di' => [
        'allowed_controllers' => [
            'Magento\Setup\Controller\Index',
            'Magento\Setup\Controller\Landing',
            'Magento\Setup\Controller\Navigation',
            'Magento\Setup\Controller\License',
            'Magento\Setup\Controller\ReadinessCheck',
            'Magento\Setup\Controller\Environment',
            'Magento\Setup\Controller\DatabaseCheck',
            'Magento\Setup\Controller\AddDatabase',
            'Magento\Setup\Controller\WebConfiguration',
            'Magento\Setup\Controller\CustomizeYourStore',
            'Magento\Setup\Controller\CreateAdminAccount',
            'Magento\Setup\Controller\Install',
            'Magento\Setup\Controller\Success',
            'Magento\Setup\Controller\ConsoleController',
        ],
        'instance' => [
            'preference' => [
                'Zend\EventManager\EventManagerInterface' => 'EventManager',
                'Zend\ServiceManager\ServiceLocatorInterface' => 'ServiceManager',
                'Magento\Framework\DB\LoggerInterface' => 'Magento\Framework\DB\Logger\Null',
            ],
        ],
    ],
];
