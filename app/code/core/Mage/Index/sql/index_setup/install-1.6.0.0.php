<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Index
 * @copyright   {copyright}
 * @license     {license_link}
 */

$installer = $this;
/* @var $installer Mage_Index_Model_Resource_Setup */

$installer->startSetup();

/**
 * Create table 'index_event'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('index_event'))
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Event Id')
    ->addColumn('type', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        'nullable'  => false,
        ), 'Type')
    ->addColumn('entity', Varien_Db_Ddl_Table::TYPE_TEXT, 64, array(
        'nullable'  => false,
        ), 'Entity')
    ->addColumn('entity_pk', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        ), 'Entity Primary Key')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
        ), 'Creation Time')
    ->addColumn('old_data', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'Old Data')
    ->addColumn('new_data', Varien_Db_Ddl_Table::TYPE_TEXT, '2M', array(
        ), 'New Data')
    ->addIndex($installer->getIdxName('index_event', array('type', 'entity', 'entity_pk'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('type', 'entity', 'entity_pk'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Index Event');
$installer->getConnection()->createTable($table);

/**
 * Create table 'index_process'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('index_process'))
    ->addColumn('process_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Process Id')
    ->addColumn('indexer_code', Varien_Db_Ddl_Table::TYPE_TEXT, 32, array(
        'nullable'  => false,
        ), 'Indexer Code')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 15, array(
        'nullable'  => false,
        'default'   => 'pending',
        ), 'BugsCoverage')
    ->addColumn('started_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Started At')
    ->addColumn('ended_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        ), 'Ended At')
    ->addColumn('mode', Varien_Db_Ddl_Table::TYPE_TEXT, 9, array(
        'nullable'  => false,
        'default'   => 'real_time',
        ), 'Mode')
    ->addIndex($installer->getIdxName('index_process', array('indexer_code'), Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE),
        array('indexer_code'), array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE))
    ->setComment('Index Process');
$installer->getConnection()->createTable($table);

/**
 * Create table 'index_process_event'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('index_process_event'))
    ->addColumn('process_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Process Id')
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_BIGINT, null, array(
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Event Id')
    ->addColumn('status', Varien_Db_Ddl_Table::TYPE_TEXT, 7, array(
        'nullable'  => false,
        'default'   => 'new',
        ), 'BugsCoverage')
    ->addIndex($installer->getIdxName('index_process_event', array('event_id')),
        array('event_id'))
    ->addForeignKey($installer->getFkName('index_process_event', 'event_id', 'index_event', 'event_id'),
        'event_id', $installer->getTable('index_event'), 'event_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('index_process_event', 'process_id', 'index_process', 'process_id'),
        'process_id', $installer->getTable('index_process'), 'process_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('Index Process Event');
$installer->getConnection()->createTable($table);

$installer->endSetup();
