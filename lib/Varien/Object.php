<?php

/**
 * Varien Object
 *
 * @package    Varien
 * @subpackage Data
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 * @author     Andrey Korolyov <andrey@varien.com>
 * @author     Moshe Gurvich <moshe@varien.com>
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */

class Varien_Object
{
    /**
     * Object attributes
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Data has been changed flag
     *
     * @var boolean
     */
    protected $_isChanged = false;

    /**
     * Deleting flag
     *
     * @var boolean
     */
    protected $_isDeleted = false;
    
    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = null;

    /**
     * Setter/Getter underscore transformation cache
     *
     * @var array
     */
    protected static $_underscoreCache = array();

    /**
     * Constructor
     *
     * By default is looking for first argument as array and assignes it as object attributes
     * This behaviour may change in child classes
     *
     */
    public function __construct()
    {
        $args = func_get_args();
        if (empty($args[0])) {
            $args[0] = array();
        }
        $this->_data = $args[0];
    }
    
    /**
     * set name of object id field
     *
     * @param   string $name
     * @return  Varien_Object
     */
    public function setIdFieldName($name)
    {
        $this->_idFieldName = $name;
        return $this;
    }
    
    /**
     * Retrieve object id
     * 
     * @return mixed
     */
    public function getId()
    {
        if ($this->_idFieldName) {
            return $this->getData($this->_idFieldName);
        }
        return $this->getData('id');
    }
    
    /**
     * Set object id field value
     *
     * @param   mixed $value
     * @return  Varien_Object
     */
    public function setId($value)
    {
        if ($this->_idFieldName) {
            $this->setData($this->_idFieldName, $value);
        }
        else {
            $this->setData('id', $value);
        }
        return $this;
    }

    /**
     * Update changed flag.
     *
     * For savers to know if the object needs to be saved.
     *
     * @param boolean $changed
     * @return Varien_Object
     */
    public function setIsChanged($changed=false)
    {
        $this->_isChanged = $changed;
        return $this;
    }

    /**
     * Returns changed flag.
     *
     * @return boolean
     */
    public function isChanged()
    {
        return $this->_isChanged;
    }

    /**
     * Update deleted flag.
     *
     * For savers to know if the row defined by the object needs to be deleted.
     *
     * @param unknown_type $deleted
     * @return unknown
     */
    public function setIsDeleted($deleted=false)
    {
        $this->_isDeleted = $deleted;
        return $this;
    }

    /**
     * Returns deleted flag.
     *
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->_isDeleted;
    }

    /**
     * Add data to the object.
     *
     * Retains previous data in the object.
     *
     * @param array $arr
     * @return Varien_Object
     */
    public function addData($arr)
    {
        foreach($arr as $index=>$value) {
            $this->setData($index, $value);
        }
        return $this;
    }

    /**
     * Overwrite data in the object.
     *
     * $key can be string or array.
     * If $key is string, the attribute value will be overwritten by $value
     *
     * If $key is an array, it will overwrite all the data in the object.
     *
     * $isChanged will specify if the object needs to be saved after an update.
     *
     * @param string|array $key
     * @param mixed $value
     * @param boolean $isChanged
     * @return Varien_Object
     */
    public function setData($key, $value='', $isChanged=true)
    {
        if ($isChanged) {
            $this->setIsChanged(true);
        }

        if(is_array($key)) {
            $this->_data = $key;
        } else {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * Unset data from the object.
     *
     * $key can be a string only. Array will be ignored.
     *
     * $isChanged will specify if the object needs to be saved after an update.
     *
     * @param string $key
     * @param boolean $isChanged
     * @return Varien_Object
     */
    public function unsetData($key, $isChanged=true)
    {
        if ($isChanged) {
            $this->setIsChanged(true);
        }

        if(is_array($key)) {
            return $this;
        } else {
            unset($this->_data[$key]);
        }
        return $this;
    }

    /**
     * Retrieves data from the object
     *
     * If $key is empty will return all the data as an array
     * Otherwise it will return value of the attribute specified by $key
     *
     * If $index is specified it will assume that attribute data is an array
     * and retrieve corresponding member.
     *
     * @param string $key
     * @param string|int $index
     * @return mixed
     */
    public function getData($key='', $index=null)
    {
        if (''===$key) {
            return $this->_data;
        } elseif (isset($this->_data[$key])) {
            if (!is_null($index)) {
                $value = $this->_data[$key];
                if (is_array($value)) {
                    return (!empty($value[$index])) ? $value[$index] : false;
                } elseif (is_string($value)) {
                    $arr = explode("\n", $value);
                    return (!empty($arr[$index])) ? $arr[$index] : false;
                } elseif ($value instanceof Varien_Object) {
                    return $value->getData($index);
                }
                return null;
            }
            return $this->_data[$key];
        }
        return null;
    }

    /**
     * If $key is empty, checks whether there's any data in the object
     * Otherwise checks if the specified attribute is set.
     *
     * @param string $key
     * @return boolean
     */
    public function hasData($key='')
    {
        if (empty($key) || !is_string($key)) {
            return !empty($this->_data);
        }
        return isset($this->_data[$key]);
    }

    /**
     * Convert object attributes to array
     *
     * @param  array $arrAttributes array of required attributes
     * @return array
     */
    public function __toArray(array $arrAttributes = array())
    {
        if (empty($arrAttributes)) {
            return $this->_data;
        }

        $arrRes = array();
        foreach ($arrAttributes as $attribute) {
            if (isset($this->_data[$attribute])) {
                $arrRes[$attribute] = $this->_data[$attribute];
            }
            else {
                $arrRes[$attribute] = null;
            }
        }
        return $arrRes;
    }

    /**
     * Public wrapper for __toArray
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray(array $arrAttributes = array())
    {
        return $this->__toArray($arrAttributes);
    }

    /**
     * Set required array elements
     *
     * @param   array $arr
     * @param   array $elements
     * @return  array
     */
    protected function _prepareArray(&$arr, array $elements=array())
    {
        foreach ($elements as $element) {
            if (!isset($arr[$element])) {
                $arr[$element] = null;
            }
        }
        return $arr;
    }

    /**
     * Convert object attributes to XML
     *
     * @param  array $arrAttributes array of required attributes
     * @param string $rootName name of the root element
     * @return string
     */
    protected function __toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag=false, $addCdata=true)
    {
        $xml = '';
        if ($addOpenTag) {
            $xml.= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        }
        $xml.= '<'.$rootName.'>'."\n";
        $arrData = $this->toArray($arrAttributes);
        foreach ($arrData as $fieldName => $fieldValue) {
            $xml.= ( $addCdata === true ) ? "<$fieldName><![CDATA[$fieldValue]]></$fieldName>"."\n" : "<$fieldName>$fieldValue</$fieldName>"."\n";
        }
        $xml.= '</'.$rootName.'>'."\n";
        return $xml;
    }

    /**
     * Public wrapper for __toXml
     *
     * @param array $arrAttributes
     * @param string $rootName
     * @return string
     */
    public function toXml(array $arrAttributes = array(), $rootName = 'item', $addOpenTag=false, $addCdata=true)
    {
        return $this->__toXml($arrAttributes, $rootName, $addOpenTag, $addCdata);
    }

    /**
     * Convert object attributes to JSON
     *
     * @param  array $arrAttributes array of required attributes
     * @return string
     */
    protected function __toJson(array $arrAttributes = array())
    {
        $arrData = $this->toArray($arrAttributes);
        $json = Zend_Json::encode($arrData);
        return $json;
    }

    /**
     * Public wrapper for __toJson
     *
     * @param array $arrAttributes
     * @return string
     */
    public function toJson(array $arrAttributes = array())
    {
        return $this->__toJson($arrAttributes);
    }

    /**
     * Convert object attributes to string
     *
     * @param  array  $arrAttributes array of required attributes
     * @param  string $valueSeparator
     * @return string
     */
    public function __toString(array $arrAttributes = array(), $valueSeparator=',')
    {
        $arrData = $this->toArray($arrAttributes);
        return implode($valueSeparator, $arrData);
    }

    /**
     * Public wrapper for __toString
     *
     * Will use $format as an template and substitute {{key}} for attributes
     *
     * @param string $format
     * @return string
     */
    public function toString($format='')
    {
        if (empty($format)) {
            $str = implode(', ', $this->getData());
        } else {
            preg_match_all('/\{\{([a-z0-9_]+)\}\}/is', $format, $matches);
            foreach ($matches[1] as $var) {
                $format = str_replace('{{'.$var.'}}', $this->getData($var), $format);
            }
            $str = $format;
        }
        return $str;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_underscore(substr($method,3));
                array_unshift($args, $key);
                return call_user_func_array(array($this, 'getData'), $args);
                break;

            case 'set' :
                $key = $this->_underscore(substr($method,3));
                array_unshift($args, $key);
                return call_user_func_array(array($this, 'setData'), $args);
                return $this;
                break;

            case 'uns' :
                $key = $this->_underscore(substr($method,5));
                array_unshift($args, $key);
                return call_user_func_array(array($this, 'unsetData'), $args);
                return $this;
                break;

            case 'has' :
                $key = $this->_underscore(substr($method,3));
                return isset($this->_data[$key]);
                break;
        }
        throw new Varien_Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
    }

    /**
     * Attribute getter (deprecated)
     *
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        $var = $this->_underscore($var);
        return $this->getData($var);
    }

    /**
     * Attribute setter (deprecated)
     *
     * @param string $var
     * @param mixed $value
     */
    public function __set($var, $value)
    {
        $this->_isChanged = true;
        $var = $this->_underscore($var);
        $this->setData($var, $value);
    }

    /**
     * checks whether the object is empty
     *
     * @return boolean
     */
    public function isEmpty()
    {
        if(empty($this->_data)) {
            return true;
        }
        return false;
    }

    /**
     * Converts field names for setters and geters
     *
     * $this->setMyField($value) === $this->setData('my_field', $value)
     * Uses cache to eliminate unneccessary preg_replace
     *
     * @param string $name
     * @return string
     */
    protected function _underscore($name)
    {
        if (isset(self::$_underscoreCache[$name])) {
            return self::$_underscoreCache[$name];
        }
        
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
        /*
        for ($i=0, $l=strlen($name), $result=''; $i<$l; $i++) {
            $c = $name{$i};
            $a = ord($c);
            if ($a>=65 && $a<=91) {
                if ($i>0) {
                    $result .= '_';
                }
                $result .= strtolower($c);
            } else {
                $result .= $c;
            }
        }
        */

        self::$_underscoreCache[$name] = $result;

        return $result;
    }

    /**
     * Serialization before saving to session
     *
     * @return array
     */
    public function __sleep()
    {
       return array_keys( (array)$this );
    }

    /**
     * Unserialization restoring from session
     *
     */
    public function __wakeup()
    {
        $this->_isChanged = false;
    }
}
