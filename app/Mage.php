<?php

/**
 * Just a shortcut for popular constant :)
 */
define ('DS', DIRECTORY_SEPARATOR);

function __autoload($class)
{
    #echo $class."<hr>";
    #Mage_Core_Profiler::setTimer('autoload');
    $classFile = str_replace(' ', DS, ucwords(str_replace('_', ' ', $class))).'.php';
    include_once($classFile);
/*    if (!include_once($classFile)) {
        $classFile = dirname(__FILE__).DS.'code'.DS.'core'.DS.$classFile;
        include_once($classFile);
    }*/
    #Mage_Core_Profiler::setTimer('autoload', true);
}

/**
 * Main Mage hub class
 *
 * @author Moshe Gurvich <moshe@varien.com>
 * @author Andrey Korolyov <andrey@varien.com>
 */
final class Mage {

    static private $_configSections = array();

    /**
     * Application root absolute path
     *
     * @var string
     */
    static private $_appRoot = null;

    /**
     * Code pools
     *
     * include_path will be built with the order these are specified
     *
     * defined in core.ini [codepools]
     * default: local, community, core
     *
     * @var array
     */
    static private $_codePools = array();

    /**
     * Collection of objects with information about modules installed
     *
     * @var array
     */
    static private $_moduleInfo = array();

    static private $_config = null;

    /**
     * Set application root absolute path
     *
     * @param string $appRoot
     */
    public static function setRoot($appRoot='')
    {
        if (''===$appRoot) {
            // automagically find application root by dirname of Mage.php
            $appRoot = dirname(__FILE__);
        }

        $appRoot = realpath($appRoot);

        if (is_dir($appRoot) and is_readable($appRoot)) {
            self::$_appRoot= $appRoot;
        } else {
            Mage::exception($appRoot.' is not a directory or not readable by this user');
        }
    }

    /**
     * Retrieve application root absolute path
     *
     * @return string
     */
    public static function getRoot($type='', $moduleName='')
    {
        $dir = self::$_appRoot;
        switch ($type) {
            case 'etc':
                $dir .= DS.'etc';
                break;
            case 'code':
                $dir .= DS.'code';
                break;
            case 'layout':
                $dir .= DS.'layout';
                break;
            case 'var':
                $dir = dirname($dir).DS.'var';
                break;
        }
        return $dir;
    }

    public static function getConfig($param='')
    {
        if (is_null(self::$_config)) {
            return false;
        }
        if (''==$param) {
            return self::$_config;
        } elseif ('/'===$param) {
            return self::$_config->getXml();
        } else {
            return self::$_config->getXpath($param);
        }
    }

    /**
     * Add a directory to code pool
     *
     * @param string $name code pool name
     * @param string $dir code pool directory
     */
    public static function addCodePool($name, $dir)
    {
        self::$_codePools[$name] = $dir;
    }

    /**
     * Retrieve code pools
     *
     * @return array
     */
    public static function getCodePools()
    {
        return self::$_codePools;
    }

    /**
     * Add Module Information
     *
     * @param Zend_Config $data
     * @param string $moduleInfoClass
     */
    public static function addModule($data, $moduleInfoClass='Mage_Core_Module_Info')
    {
        if (empty($data) || !is_object($data)) {
            Mage::exception('Invalid data');
        }

        if ($moduleInfoClass!='Mage_Core_Module_Info'
        && is_subclass_of($moduleInfoClass, 'Mage_Core_Module_Info')) {
            Mage::exception('Invalid module info class name');
        }

        self::$_moduleInfo[strtolower($data['name'])] = new $moduleInfoClass($data);
    }

    /**
     * Retrieve module information object
     *
     * @param string $module
     * @return Mage_Core_Module_Info
     */
    public static function getModuleInfo($module='')
    {
        $module = strtolower($module);
        if (''===$module) {
            return self::$_moduleInfo;
        } else {
            if (isset(self::$_moduleInfo[$module])) {
                return self::$_moduleInfo[$module];
            } else {
                return false;
            }
        }
    }

    /**
     * Get module configuration, loaded from config files
     *
     * @param string $module
     * @param string $key
     * @return Zend_Config_Ini
     */
    public static function getModuleConfig($module)
    {
        return Mage_Core_Config::getModule($module);
    }

    /**
     * Add handler for parsing a config file section
     *
     * @param string $name
     * @param callback $callback
     */
    static function addConfigSection($name, $callback)
    {
        self::$_configSections[$name] = $callback;
    }

    /**
     * Retrieve config section handler class
     *
     * @param string $name
     * @return string
     */
    static function getConfigSection($name='')
    {
        if (''===$name) {
            return self::$_configSections;
        } else {
            if (isset(self::$_configSections[$name])) {
                return self::$_configSections[$name];
            }
        }
        return false;
    }

    /**
     * Retrieve event object
     *
     * @param string $name
     * @return Mage_Core_Event
     */
    public static function getEvent($name)
    {
        return Mage_Core_Event::getEvent($name);
    }

    /**
     * Add event object
     *
     * @param unknown_type $name
     */
    public static function addEvent($name)
    {
        return Mage_Core_Event::addEvent($name);
    }

    /**
     * Add observer to even object
     *
     * @param string $eventName
     * @param callback $callback
     * @param array $arguments
     * @param string $observerName
     */
    public static function addObserver($eventName, $callback, array $arguments=array(), $observerName='')
    {
        return Mage_Core_Event::addObserver($eventName, $callback, $arguments, $observerName);
    }

    /**
     * Add observer to watch for multiple events matching regex pattern
     *
     * @param string $eventRegex
     * @param callback $callback
     */
    public static function addMultiObserver($eventRegex, $callback, $observerName='')
    {
        return Mage_Core_Event::addMultiObserver($eventRegex, $callback, $observerName);
    }

    /**
     * Dispatch event
     *
     * Calls all observer callbacks registered for this event
     * and multiobservers matching event name pattern
     *
     * @param string $name
     * @param array $args
     */
    public static function dispatchEvent($name, array $args=array())
    {
        return Mage_Core_Event::dispatchEvent($name, $args);
    }

    public static function getController()
    {
        return Mage_Core_Controller::getController();
    }


    /**
     * Retrieve active modules
     *
     * @return array
     */
    public static function getActiveModules()
    {
        return self::getModuleInfo();
    }

    /**
     * Load active modules
     * 
     * @param   mixed $configs
     */
    public static function loadActiveModules($configs = '')
    {
        $modules = self::getActiveModules();
        foreach ($modules as $modName=>$modInfo) {
            $modInfo->loadConfig('load');
            $modInfo->loadConfig('*user*');
            if (!empty($configs)) {
                if (is_array($configs)) {
                    foreach ($configs as $config) {
                        $modInfo->loadConfig($config);
                    }
                }
                else {
                    $modInfo->loadConfig($configs);
                }
            }
            $modInfo->processConfig();
        }
    }

    /**
     * Create page block
     *
     * See {@link Mage_Core_Block::createBlock}
     *
     * @param string $type
     * @param string $name
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    public static function createBlock($type, $name='', array $attributes=array())
    {
        return Mage_Core_Block::createBlock($type, $name, $attributes);
    }

    /**
     * Create block from template
     *
     * @link Mage_Core_Block::createBlockLike
     * @param string $template
     * @param string $name
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    public static function createBlockLike($template, $name='', array $attributes=array())
    {
        return Mage_Core_Block::createBlockLike($template, $name, $attributes);
    }

    /**
     * Return Block object for block id
     *
     * @link    Mage_Core_Block::getBlockByName
     * @param   string $id
     * @return  Mage_Core_Block_Abstract
     */
    public static function getBlock($name)
    {
        return Mage_Core_Block::getBlockByName($name);
    }

    /**
     * Get base URL path by type
     *
     * @param string $type
     * @return string
     */
    public static function getBaseUrl($type='')
    {
        return Mage_Core_Controller::getBaseUrl($type);
    }

    /**
	 * Get model class
	 *
	 * @link Mage_Core_Model::getModelClass
	 * @param string $model
	 * @param string $class
	 * @param array $arguments
	 * @return Mage_Core_Model_Abstract
	 */
    public static function getModel($model, $class='', array $arguments=array())
    {
        return Mage_Core_Model::getModelClass($model, $class, $arguments);
    }

    public static function getCurentWebsite()
    {
        return Mage_Core_Website::getWebsiteId();
    }

    /**
	 * Throw new exception by module
	 *
	 * @param string $message
	 * @param integer $code
	 * @param string $module
	 */
    public static function exception($message, $code=0, $module='Mage_Core')
    {
        $className = $module.'_Exception';
        throw new $className($message, $code);
    }

    public static function prepareFileSystem()
    {
        $xmlCacheDir = Mage::getRoot('var').DS.'cache'.DS.'xml';
        if (!is_writable($xmlCacheDir)) {
            mkdir($xmlCacheDir, 0777, true);
        }
        $logDir =  Mage::getRoot('var').DS.'log';
        if (!is_writable($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }

    /**
     * Initialize Mage
     *
     * @param string $appRoot
     */
    public static function init($appRoot='')
    {
        Mage::setRoot($appRoot);

        Mage::prepareFileSystem();
        Mage_Core_Profiler::setTimer('app');

        self::$_config = new Mage_Core_Config();
        
        // check modules db
        self::$_config->checkModulesDbChanges();
        #echo Mage_Core_Profiler::setTimer('app').',';
    }

    /**
     * Mage main entry point
     *
     * @param string $appRoot
     */
    public static function run($appRoot='')
    {
        try {
            self::init($appRoot);
        
            self::getConfig()->loadEventObservers('front');

            #Mage_Core_Profiler::setTimer('zend_controller');
            Mage_Core_Controller::setController(new Mage_Core_Controller_Zend());
            #Mage_Core_Profiler::setTimer('zend_controller', true);
            #self::loadActiveModules('front_load');
            Mage_Core_Controller::getController()->run();

            Mage_Core_Profiler::getTimer('app', true);
            Mage_Core_Profiler::getSqlProfiler();
        } catch (Zend_Exception $e) {
            echo $e->getMessage()."<pre>".$e->getTraceAsString();
        } catch (PDOException $e) {
            echo $e->getMessage()."<pre>".$e->getTraceAsString();
        }
    }

    public static function runAdmin($appRoot='')
    {
        // temp (for test)
        session_start();
        try {
            self::init($appRoot);
        
            self::getConfig()->loadEventObservers('admin');
            
            Mage_Core_Controller::setController(new Mage_Core_Controller_Zend_Admin());
            #self::loadActiveModules('admin_load');
            Mage_Core_Controller::getController()->run();

            //Mage_Core_Profiler::getTimer('app', true);
            //  Mage_Core_Profiler::getSqlProfiler();
        } catch (Zend_Exception $e) {
            echo $e->getMessage()."<pre>".$e->getTraceAsString();
        } catch (PDOException $e) {
            echo $e->getMessage()."<pre>".$e->getTraceAsString();
        }
    }

    public static function test()
    {
        self::init();

        Mage_Core_Profiler::setTimer('config');
        self::$_config = new Mage_Core_Config();
        echo Mage_Core_Profiler::setTimer('config');

        echo "<xmp>TEST:";
        print_r(Mage::getConfig());
    }
    
    public static function log($message, $level=Zend_Log::LEVEL_DEBUG, $file = '')
    {
        if (empty($file)) {
            $file = 'system.log';
        }
        $logFile = Mage::getRoot('var').DS.'log'.DS.$file;
        
        if (!Zend_Log::hasLogger($file)) {
            Zend_Log::registerLogger(new Zend_Log_Adapter_File($logFile), $file);
        }
        
        Zend_Log::log($message, $level, $file);
    }
}