<?php
/**
 * {license_notice}
 *
 * @category    Magento
 * @package     tools
 * @subpackage  batch_tests
 * @copyright   {copyright}
 * @license     {license_link}
 */

$commands = array(
    'unit'                  => array('../../tests/unit', ''),
    'unit-performance'      => array('../../tests/performance/framework/tests/unit', ''),
    'unit-static'           => array('../../tests/static/framework/tests/unit', ''),
    'unit-integration'      => array('../../tests/integration/framework/tests/unit', ''),
    'integration'           => array('../../tests/integration', ''),
    'integration-integrity' => array('../../tests/integration', ' testsuite/integrity'),
    'static-default'        => array('../../tests/static', ''),
    'static-legacy'         => array('../../tests/static', ' testsuite/Legacy'),
    'static-integration'    => array('../../tests/static', ' testsuite/Exemplar'),
);
$types = array(
    'all'             => array(), // will merge automatically, see below
    'unit'            => array('unit', 'unit-performance', 'unit-static', 'unit-integration'),
    'integration'     => array('integration'),
    'integration-all' => array('integration', 'integration-integrity'),
    'static'          => array('static-default'),
    'static-all'      => array('static-default', 'static-legacy', 'static-integration'),
    'integrity'       => array('static-default', 'static-legacy', 'integration-integrity'),
    'legacy'          => array('static-legacy'),
    'default'         => array(
        'unit', 'unit-performance', 'unit-static', 'unit-integration', 'integration', 'static-default'
    ),
);
foreach ($types as $type) {
    $types['all'] = array_unique(array_merge($types['all'], $type));
}

$arguments = getopt('', array('type::'));
if (!isset($arguments['type'])) {
    $arguments['type'] = 'default';
} elseif (!isset($types[$arguments['type']])) {
    echo "Invalid type: '{$arguments['type']}'. Available types: " . implode(', ', array_keys($types)) . "\n\n";
    exit(1);
}

$failures = array();
$runCommands = $types[$arguments['type']];
foreach ($runCommands as $key) {
    list($dir, $options) = $commands[$key];
    $dirName = realpath(__DIR__ . '/' . $dir);
    chdir($dirName);
    $command = 'phpunit' . $options;
    $message = $dirName . '> ' . $command;
    echo "\n\n";
    echo str_pad("---- {$message} ", 70, '-');
    echo "\n\n";
    passthru($command, $returnVal);
    if ($returnVal) {
        $failures[] = $message;
    }
}

echo "\n" , str_repeat('-', 70), "\n";
if ($failures) {
    echo "\nFAILED - " . count($failures) . ' of ' . count($runCommands) . ":\n";
    foreach ($failures as $message) {
        echo ' - ' . $message . "\n";
    }
} else {
    echo "\nPASSED (" . count($runCommands) . ")\n";
}
