<?php
/**
 * Entry point for upgrading application in SaaS environment
 *
 * Interface:
 * - SaaS infrastructure includes this file and executes as a callback
 * - The callback receives one argument: an array with various application configuration, adjusted for the tenant
 */
/**
 * @param array $params
 */
return function (array $params)
{
    $rootDir = dirname(__DIR__);
    require $rootDir . '/app/bootstrap.php';
    $config = new Saas_Saas_Model_Tenant_Config($rootDir, $params);
    $appParams = $config->getApplicationParams();
    if (isset($params['tmt_reindex_mode'])) {
        $appParams[Mage_Install_Model_EntryPoint_Upgrade::REINDEX] = $params['tmt_reindex_mode'];
    }
    $entryPoint = new Mage_Install_Model_EntryPoint_Upgrade($rootDir, $appParams);
    $entryPoint->processRequest();
};
