<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * A proxy for Fallback model. This proxy processes fallback resolution calls by either using map of cached paths, or
 * passing resolution to the Fallback model.
 */
class Mage_Core_Model_Design_Fallback_CachingProxy implements Mage_Core_Model_Design_FallbackInterface
{
    /**
     * @var string
     */
    protected $_area;

    /**
     * @var Mage_Core_Model_Theme
     */
    protected $_theme;

    /**
     * @var string|null
     */
    protected $_locale;

    /**
     * Whether object can save map changes upon destruction
     *
     * @var bool
     */
    protected $_canSaveMap;

    /**
     * Whether there were changes in map
     *
     * @var bool
     */
    protected $_isMapChanged = false;

    /**
     * Map full filename
     *
     * @var string
     */
    protected $_mapFile;

    /**
     * Cached fallback map
     *
     * @var array
     */
    protected $_map;

    /**
     * Proxied fallback model
     *
     * @var Mage_Core_Model_Design_Fallback
     */
    protected $_fallback;

    /**
     * Directory to keep map file
     *
     * @var string
     */
    protected $_mapDir;

    /**
     * Path to Magento base directory
     *
     * @var string
     */
    protected $_basePath;

    /**
     * @var Magento_Filesystem
     */
    protected $_filesystem;

    /**
     * Constructor.
     * Following entries in $params are required: 'area', 'package', 'theme', 'locale', 'canSaveMap',
     * 'mapDir', 'baseDir'.
     *
     * @param Magento_Filesystem $filesystem
     * @param array $data
     */
    public function __construct(Magento_Filesystem $filesystem, array $data = array())
    {
        $this->_filesystem = $filesystem;
        $this->_area = $data['area'];
        $this->_theme = $data['themeModel'];
        $this->_locale = $data['locale'];
        $this->_canSaveMap = $data['canSaveMap'];
        $this->_mapDir = $data['mapDir'];
        $this->_basePath = $data['baseDir'] ? $data['baseDir'] . DIRECTORY_SEPARATOR : '';

        $this->_mapFile =
            "{$this->_mapDir}/{$this->_area}_{$this->_theme->getId()}_{$this->_locale}.ser";
        $this->_map = $this->_filesystem->isFile($this->_mapFile)
            ? unserialize($this->_filesystem->read($this->_mapFile))
            : array();
    }

    public function __destruct()
    {
        if ($this->_isMapChanged && $this->_canSaveMap) {
            if (!$this->_filesystem->isDirectory($this->_mapDir)) {
                $this->_filesystem->createDirectory($this->_mapDir, 0777);
            }
            $this->_filesystem->write($this->_mapFile, serialize($this->_map));
        }
    }

    /**
     * Return instance of fallback model. Create it, if it has not been created yet.
     *
     * @return Mage_Core_Model_Design_Fallback
     */
    protected function _getFallback()
    {
        if (!$this->_fallback) {
            $this->_fallback = Mage::getModel('Mage_Core_Model_Design_Fallback', array(
                'data' => array(
                    'area'       => $this->_area,
                    'themeModel' => $this->_theme,
                    'locale'     => $this->_locale
                )
            ));
        }
        return $this->_fallback;
    }

    /**
     * Return relative file name from map
     *
     * @param string $prefix
     * @param string $file
     * @param string|null $module
     * @return string|null
     */
    protected function _getFromMap($prefix, $file, $module = null)
    {
        $mapKey = "$prefix|$file|$module";
        if (isset($this->_map[$mapKey])) {
            $value =  $this->_map[$mapKey];
            if ((string) $value !== '') {
                return $this->_basePath . $value;
            } else {
                return $value;
            }
        } else {
            return null;
        }
    }

    /**
     * Sets file to map. The file path must be within baseDir path.
     *
     * @param string $prefix
     * @param string $file
     * @param string|null $module
     * @param string $filePath
     * @return Mage_Core_Model_Design_Fallback_CachingProxy
     * @throws Mage_Core_Exception
     */
    protected function _setToMap($prefix, $file, $module, $filePath)
    {
        $basePathLen = strlen($this->_basePath);
        if (((string)$filePath !== '') && strncmp($filePath, $this->_basePath, $basePathLen)) {
            throw new Mage_Core_Exception(
                "Attempt to store fallback path '{$filePath}', which is not within '{$this->_basePath}'"
            );
        }

        $mapKey = "$prefix|$file|$module";
        $this->_map[$mapKey] = substr($filePath, $basePathLen);
        $this->_isMapChanged = true;
        return $this;
    }

    /**
     * Get existing file name, using map and fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getFile($file, $module = null)
    {
        $result = $this->_getFromMap('theme', $file, $module);
        if (!$result) {
            $result = $this->_getFallback()->getFile($file, $module);
            $this->_setToMap('theme', $file, $module, $result);
        }
        return $result;
    }

    /**
     * Get locale file name, using map and fallback mechanism
     *
     * @param string $file
     * @return string
     */
    public function getLocaleFile($file)
    {
        $result = $this->_getFromMap('locale', $file);
        if (!$result) {
            $result = $this->_getFallback()->getLocaleFile($file);
            $this->_setToMap('locale', $file, null, $result);
        }
        return $result;
    }

    /**
     * Get Theme file name, using map and fallback mechanism
     *
     * @param string $file
     * @param string|null $module
     * @return string
     */
    public function getViewFile($file, $module = null)
    {
        $result = $this->_getFromMap('view', $file, $module);
        if (!$result) {
            $result = $this->_getFallback()->getViewFile($file, $module);
            $this->_setToMap('view', $file, $module, $result);
        }
        return $result;
    }

    /**
     * Object notified, that theme file was published, thus it can return published file name on next calls
     *
     * @param string $publicFilePath
     * @param string $file
     * @param string|null $module
     * @return Mage_Core_Model_Design_FallbackInterface
     */
    public function notifyViewFilePublished($publicFilePath, $file, $module = null)
    {
        return $this->_setToMap('view', $file, $module, $publicFilePath);
    }
}
