<?php
/**
 * {license_notice}
 *
 * @copyright {copyright}
 * @license   {license_link}
 */
namespace Magento\Tools\Di\Compiler;
use Zend\Code\Scanner\FileScanner,
    Magento\Tools\Di\Compiler\Log\Log;

class Directory
{
    /**
     * @var array
     */
    protected $_processedClasses = array();

    /**
     * @var array
     */
    protected $_definitions = array();

    /**
     * @var string
     */
    protected $_current;

    /**
     * @var Log
     */
    protected $_log;

    /**
     * @param Log $log
     */
    public function __construct(Log $log)
    {
        $this->_log = $log;
        set_error_handler(array($this, 'errorHandler'), E_STRICT);
    }

    /**
     * @param int $errno
     * @param string $errstr
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function errorHandler($errno, $errstr)
    {
        $this->_log->add(Log::COMPILATION_ERROR, $this->_current, $errstr);
    }

    /**
     * @param string $path
     */
    public function compile($path)
    {
        $rdi = new \RecursiveDirectoryIterator(realpath($path));
        $recursiveIterator = new \RecursiveIteratorIterator($rdi, 1);
        /** @var $item \SplFileInfo */
        foreach ($recursiveIterator as $item) {
            if ($item->isFile() && pathinfo($item->getRealPath(), PATHINFO_EXTENSION) == 'php') {
                $fileScanner = new FileScanner($item->getRealPath());
                $classNames = $fileScanner->getClassNames();
                foreach ($classNames as $className) {
                    $this->_current = $className;
                    if (!class_exists($className)) {
                        require_once $item->getRealPath();
                    }
                    try {
                        $signatureReader = new \Magento_Code_Reader_ClassReader();
                        $this->_definitions[$className] = $signatureReader->getConstructor($className);
                    } catch (\ReflectionException $e) {
                        $this->_log->add(Log::COMPILATION_ERROR, $className, $e->getMessage());
                    }
                    $this->_processedClasses[$className] = 1;
                }
            }
        }
    }

    /**
     * Retrieve compilation result
     *
     * @return array
     */
    public function getResult()
    {
        return $this->_definitions;
    }
}
