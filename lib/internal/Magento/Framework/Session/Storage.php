<?php
/**
 * Default session storage
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Session;

class Storage extends \Magento\Framework\Object implements StorageInterface
{
    /**
     * Namespace of storage
     *
     * @var string
     */
    protected $namespace;

    /**
     * Constructor
     *
     * @param string $namespace
     * @param array $data
     */
    public function __construct($namespace = 'default', array $data = [])
    {
        $this->namespace = $namespace;
        parent::__construct($data);
    }

    /**
     * {@inheritdoc}
     */
    public function init(array $data)
    {
        $namespace = $this->getNamespace();
        if (isset($data[$namespace])) {
            $this->setData($data[$namespace]);
        }
        $_SESSION[$namespace] = & $this->_data;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Additional get data with clear mode
     *
     * @param string $key
     * @param bool $clear
     * @return mixed
     */
    public function getData($key = '', $clear = false)
    {
        $data = parent::getData($key);
        if ($clear && isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return $data;
    }
}
