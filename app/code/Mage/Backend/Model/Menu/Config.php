<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Backend_Model_Menu_Config
{
    const CACHE_ID = 'backend_menu_config';
    const CACHE_MENU_OBJECT = 'backend_menu_object';

    /**
     * @var Mage_Core_Model_Cache_Type_Config
     */
    protected $_configCacheType;

    /**
     * @var Magento_ObjectManager
     */
    protected $_factory;

    /**
     * @var Mage_Core_Model_Config_Modules_Reader
     */
    protected $_moduleReader;

    /**
     * @var Mage_Core_Model_Event_Manager
     */
    protected $_eventManager;

    /**
     * @var Mage_Backend_Model_Menu_Factory
     */
    protected $_menuFactory;
    /**
     * Menu model
     *
     * @var Mage_Backend_Model_Menu
     */
    protected $_menu;

    /**
     * @var Mage_Core_Model_Logger
     */
    protected $_logger;

    /**
     * @param Mage_Core_Model_Cache_Type_Config $configCacheType
     * @param Magento_ObjectManager $factory
     * @param Mage_Core_Model_Config_Modules_Reader $moduleReader
     * @param Mage_Core_Model_Event_Manager $eventManager
     * @param Mage_Core_Model_Logger $logger
     * @param Mage_Backend_Model_Menu_Factory $menuFactory
     */
    public function __construct(
        Mage_Core_Model_Cache_Type_Config $configCacheType,
        Magento_ObjectManager $factory,
        Mage_Core_Model_Config_Modules_Reader $moduleReader,
        Mage_Core_Model_Event_Manager $eventManager,
        Mage_Core_Model_Logger $logger,
        Mage_Backend_Model_Menu_Factory $menuFactory
    ) {
        $this->_configCacheType = $configCacheType;
        $this->_factory = $factory;
        $this->_moduleReader = $moduleReader;
        $this->_eventManager = $eventManager;
        $this->_logger = $logger;
        $this->_menuFactory = $menuFactory;
    }

    /**
     * Build menu model from config
     *
     * @return Mage_Backend_Model_Menu
     * @throws InvalidArgumentException|BadMethodCallException|OutOfRangeException|Exception
     */
    public function getMenu()
    {
        $store = $this->_factory->get('Mage_Core_Model_App')->getStore();
        $this->_logger->addStoreLog(Mage_Backend_Model_Menu::LOGGER_KEY, $store);
        try {
            $this->_initMenu();
            return $this->_menu;
        } catch (InvalidArgumentException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (BadMethodCallException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (OutOfRangeException $e) {
            $this->_logger->logException($e);
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Initialize menu object
     *
     * @return void
     */
    protected function _initMenu()
    {
        if (!$this->_menu) {
            $this->_menu = $this->_menuFactory->getMenuInstance();

            $cache = $this->_configCacheType->load(self::CACHE_MENU_OBJECT);
            if ($cache) {
                $this->_menu->unserialize($cache);
                return;
            }

            /* @var $director Mage_Backend_Model_Menu_Builder */
            $menuBuilder = $this->_factory->create('Mage_Backend_Model_Menu_Builder', array(
                'menu' => $this->_menu,
                'menuItemFactory' => $this->_factory->get('Mage_Backend_Model_Menu_Item_Factory'),
            ));

            /* @var $director Mage_Backend_Model_Menu_Director_Dom */
            $director = $this->_factory->create(
                'Mage_Backend_Model_Menu_Director_Dom',
                array(
                    'menuConfig' => $this->_getDom(),
                    'factory' => $this->_factory,
                    'menuLogger' => $this->_logger
                )
            );
            $director->buildMenu($menuBuilder);
            $this->_menu = $menuBuilder->getResult();
            $this->_eventManager->dispatch('backend_menu_load_after', array('menu' => $this->_menu));

            $this->_configCacheType->save($this->_menu->serialize(), self::CACHE_MENU_OBJECT);
        }
    }

    /**
     * @return DOMDocument
     */
    protected function _getDom()
    {
        $mergedConfigXml = $this->_loadCache();
        if ($mergedConfigXml) {
            $mergedConfig = new DOMDocument();
            $mergedConfig->loadXML($mergedConfigXml);
        } else {
            $fileList = $this->getMenuConfigurationFiles();
            $mergedConfig = $this->_factory
                ->create('Mage_Backend_Model_Menu_Config_Menu', array('configFiles' => $fileList))
                ->getMergedConfig();
            $this->_saveCache($mergedConfig->saveXML());
        }
        return $mergedConfig;
    }

    protected function _loadCache()
    {
        return $this->_configCacheType->load(self::CACHE_ID);
    }

    protected function _saveCache($xml)
    {
        $this->_configCacheType->save($xml, self::CACHE_ID);
        return $this;
    }

    /**
     * Return array menu configuration files
     *
     * @return array
     */
    public function getMenuConfigurationFiles()
    {
        $files = $this->_moduleReader
            ->getModuleConfigurationFiles('adminhtml' . DIRECTORY_SEPARATOR . 'menu.xml');
        return (array) $files;
    }
}
