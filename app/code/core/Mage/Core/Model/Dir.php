<?php
/**
 * Application file system directories dictionary
 *
 * Provides information about what directories are available in the application
 * Serves as customizaiton point to specify different directories or add own
 *
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
class Mage_Core_Model_Dir
{
    /**#@+
     * Dictionary of available directory codes
     */
    const ROOT    = 'base';
    const APP     = 'app';
    const CODE    = 'code';
    const VIEW    = 'design';
    const CONFIG  = 'etc';
    const LIB     = 'lib';
    const LOCALE  = 'locale';
    const PUB     = 'pub';
    const PUB_LIB = 'pub_lib';
    const MEDIA   = 'media';
    const VAR_DIR = 'var';
    const TMP     = 'tmp';
    const CACHE   = 'cache';
    const LOG     = 'log';
    const SESSION = 'session';
    const UPLOAD  = 'upload';
    const EXPORT  = 'export';
    /**#@-*/

    /**
     * Default values for directories (and URIs)
     *
     * Format: array(<code> => <relative_path>)
     *
     * @var array
     */
    private static $_defaults = array(
        self::ROOT    => '',
        self::APP     => 'app',
        self::CODE    => 'app/code',
        self::VIEW    => 'app/design',
        self::CONFIG  => 'app/etc',
        self::LIB     => 'lib',
        self::LOCALE  => 'app/locale',
        self::VAR_DIR => 'var',
        self::TMP     => 'var/tmp',
        self::CACHE   => 'var/cache',
        self::LOG     => 'var/log',
        self::SESSION => 'var/session',
        self::EXPORT  => 'var/export',
        self::PUB     => 'pub',
        self::PUB_LIB => 'pub/lib',
        self::MEDIA   => 'pub/media',
        self::UPLOAD  => 'pub/media/upload',
    );

    /**
     * Paths of URIs designed for building URLs
     *
     * @var array
     */
    private $_uris = array(
        self::PUB     => self::PUB,
        self::PUB_LIB => self::PUB_LIB,
        self::MEDIA   => self::MEDIA,
        self::UPLOAD  => self::UPLOAD,
    );

    /**
     * Absolute paths to directories
     *
     * @var array
     */
    private $_dirs = array();

    /**
     * List of directories that application requires to be writable in order to operate
     *
     * @return array
     */
    public static function getWritableDirCodes()
    {
        return array(self::MEDIA, self::VAR_DIR, self::TMP, self::CACHE, self::LOG, self::SESSION, self::EXPORT);
    }

    /**
     * Initialize URIs and paths
     *
     * @param string $baseDir
     * @param array $uris custom URIs
     * @param array $dirs custom directories (full system paths)
     */
    public function __construct($baseDir, array $uris = array(), array $dirs = array())
    {
        // uris
        foreach ($this->_uris as $code) {
            $this->_uris[$code] = self::$_defaults[$code];
        }
        foreach ($uris as $code => $uri) {
            $this->_setUri($code, $uri);
        }
        foreach ($this->_getDefaultReplacements($uris) as $code => $replacement) {
            $this->_setUri($code, $replacement);
        }

        // dirs
        foreach (self::$_defaults as $code => $path) {
            $this->_setDir($code, $baseDir . ($path ? DIRECTORY_SEPARATOR . $path : ''));
        }
        foreach ($dirs as $code => $path) {
            $this->_setDir($code, $path);
        }
        foreach ($this->_getDefaultReplacements($dirs) as $code => $replacement) {
            $this->_setDir($code, $replacement);
        }
    }

    /**
     * URI getter
     *
     * @param string $code
     * @return string|bool
     */
    public function getUri($code)
    {
        return isset($this->_uris[$code]) ? $this->_uris[$code] : false;
    }

    /**
     * Set URI
     *
     * The method is private on purpose: it must be used only in constructor. Users of this object must not be able
     * to alter its state, otherwise it may compromise application integrity.
     * Path must be usable as a fragment of a URL path.
     * For interoperability and security purposes, no uppercase or "upper directory" paths like "." or ".."
     *
     * @param $code
     * @param $uri
     * @throws InvalidArgumentException
     */
    private function _setUri($code, $uri)
    {
        if (!preg_match('/^([a-z0-9_]+[a-z0-9\._]*(\/[a-z0-9_]+[a-z0-9\._]*)*)?$/', $uri)) {
            throw new InvalidArgumentException(
                "Must be relative directory path in lowercase with '/' directory separator: '{$uri}'"
            );
        }
        $this->_uris[$code] = $uri;
    }

    /**
     * Directory path getter
     *
     * @param string $code
     * @return string|bool
     */
    public function getDir($code)
    {
        return isset($this->_dirs[$code]) ? $this->_dirs[$code] : false;
    }

    /**
     * Set directory
     *
     * @param string $code
     * @param string $path
     */
    private function _setDir($code, $path)
    {
        $this->_dirs[$code] = str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $path);
    }

    /**
     * Using default relations, find replacements for child directories if their parent has changed
     *
     * For example, "var" has children "var/tmp" and "var/cache". If "var" is customized as "var.test", and its children
     * are not, then they will be automatically replaced to "var.test/tmp" and "var.test/cache"
     *
     * @param array $source
     * @return array
     */
    private function _getDefaultReplacements(array $source)
    {
        $result = array();
        foreach ($source as $parentCode => $parent) {
            foreach ($this->_getChildren($parentCode) as $childCode) {
                if (!isset($source[$childCode])) {
                    $fragment = str_replace(self::$_defaults[$parentCode], '', self::$_defaults[$childCode]);
                    $result[$childCode] = $parent ? $parent . $fragment : $parent . ltrim($fragment, '/');
                }
            }
        }
        return $result;
    }

    /**
     * Analyze defaults and determine child codes of specified element
     *
     * @param string $code
     * @return array
     */
    private function _getChildren($code)
    {
        $result = array();
        $parent = self::$_defaults[$code];
        foreach (self::$_defaults as $childCode => $child) {
            if ($parent && $child && ($parent != $child) && 0 === strpos($child, $parent)) {
                $result[] = $childCode;
            }
        }
        return $result;
    }
}
