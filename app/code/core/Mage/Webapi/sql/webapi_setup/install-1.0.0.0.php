<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webapi
 * @copyright   {copyright}
 * @license     {license_link}
 */

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('webapi_role'))
    ->addColumn(
        'role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,),
        'Webapi role ID')
    ->addColumn(
        'role_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,),
        'Role name is displayed in Adminhtml interface')
    ->addIndex(
        $installer->getIdxName('webapi_role', array('role_name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('role_name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Roles of unified webapi ACL');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('webapi_user'))
    ->addColumn(
        'user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true,),
        'Webapi user ID')
    ->addColumn(
        'user_name', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,),
        'User name is displayed in Adminhtml interface')
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned'  => true,
            'default'   => null,
            'nullable'  => true),
        'User role from webapi_role')
    ->addIndex(
        $installer->getIdxName('webapi_user', array('role_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('role_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
    ->addIndex(
        $installer->getIdxName('webapi_user', array('user_name'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('user_name'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->addForeignKey(
        $installer->getFkName('webapi_user', 'role_id', 'webapi_role', 'role_id'),
        'role_id',
        $installer->getTable('webapi_role'),
        'role_id',
        Varien_Db_Ddl_Table::ACTION_SET_NULL,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Users of unified webapi');
$installer->getConnection()->createTable($table);

$table = $installer->getConnection()
    ->newTable($installer->getTable('webapi_rule'))
    ->addColumn(
        'rule_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'identity'  => true,
            'unsigned'  => true,
            'nullable'  => false,
            'primary'   => true),
        'Rule ID')
    ->addColumn(
        'resource_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255,
        array(
            'nullable'  => false,),
        'Resource name. Must match resource calls in xml.')
    ->addColumn('role_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null,
        array(
            'unsigned'  => true,
            'nullable'  => false),
        'User role from webapi_role')
    ->addIndex(
        $installer->getIdxName('webapi_rule', array('role_id'), Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX),
        array('role_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_INDEX))
    ->addForeignKey(
        $installer->getFkName('webapi_rule', 'role_id', 'webapi_role', 'role_id'),
        'role_id',
        $installer->getTable('webapi_role'),
        'role_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Permissions of roles to resources');
$installer->getConnection()->createTable($table);

$installer->endSetup();
