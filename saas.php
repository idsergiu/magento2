<?php
/**
 * SaaS application "entry point", requires "SaaS access point" to delegate execution to it
 *
 * {license_notice}
 *
 * @copyright  {copyright}
 * @license    {license_link}
 */

/**
 * Run application based on invariant configuration string
 *
 * Both "SaaS access point" and this "entry point" have a convention: API consists of one and only one string argument
 * Underlying implementation may differ, in future versions of the entry point, but API should remain the same
 *
 * @param string $appConfigString
 */
return function ($appConfigString) {
    try {
        $params = array_merge($_SERVER, unserialize($appConfigString));
        require __DIR__ . '/app/bootstrap.php';
        Magento_Profiler::start('mage');
        if (!array_key_exists(Mage::PARAM_BASEDIR, $params)) {
            $params[Mage::PARAM_BASEDIR] = BP;
        }

        $config = new Saas_Core_Model_ObjectManager_Config($params);
        $objectManager = new Mage_Core_Model_ObjectManager($config, BP);
        $entryPoint = new Mage_Core_Model_EntryPoint_Http(BP, $params, $objectManager);
        $entryPoint->processRequest();
        Magento_Profiler::stop('mage');
    } catch (Exception $e) {
        Mage::printException($e);
    }
};
