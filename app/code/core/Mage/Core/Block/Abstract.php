<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   Mage
 * @package    Mage_Core
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Base Content Block class
 *
 * For block generation you must define Data source class, data source class method,
 * parameters array and block template
 *
 * @author     Moshe Gurvich <moshe@varien.com>
 * @author     Soroka Dmitriy <dmitriy@varien.com>
 */

abstract class Mage_Core_Block_Abstract extends Varien_Object
{
    /**
     * Parent layout of the block
     *
     * @var Mage_Core_Model_Layout
     */
    protected $_layout = null;

    /**
     * Contains references to child block objects
     *
     * @var array
     */
    protected $_children = array();

    /**
     * Children blocks HTML cache array
     *
     * @var array
     */
    protected $_childrenHtmlCache = array();

    /**
     * Request object
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;

    /**
     * Messages block instance
     *
     * @var Mage_Core_Block_Messages
     */
    protected $_messagesBlock = null;

    protected $_helpers = array();

    public function __construct($attributes=array())
    {
        parent::__construct($attributes);

        if (Mage::registry('controller')) {
            $this->_request = Mage::registry('controller')->getRequest();
        }
        else {
            throw new Exception("Can't retrieve request object");
        }

        $this->_construct();
    }

    protected function _construct() {}

    /**
     * Retrieve request object
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * Retrieve current action object
     *
     * @return Mage_Core_Controller_Varien_Action
     */
    public function getAction()
    {
        return Mage::registry('action');
    }

    /**
     * Set layout object
     *
     * @param   Mage_Core_Model_Layout $layout
     * @return  Mage_Core_Block_Abstract
     */
    public function setLayout(Mage_Core_Model_Layout $layout)
    {
        $this->_layout = $layout;
        $this->_prepareLayout();
        return $this;
    }

    /**
     * Preparing global layout
     *
     * You can redefine this method in child classes for changin layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        return $this;
    }

    /**
     * Retrieve layout object
     *
     * @return Mage_Core_Model_Layout
     */
    public function getLayout()
    {
        return $this->_layout;
    }
    
    /**
     * Retrieve data of design package configuratio
     *
     * @param   string $path
     * @return  mixed
     */
    public function getDesignConfig($path)
    {
    	return Mage::getDesign()->getConfig($path);
    }
    
    /**
     * Retrieve block attribute value
     * 
     * Wrapper for method "getData"
     *
     * @param   string $name
     * @return  mixed
     */
    public function getAttribute($name)
    {
        return $this->getData($name);
    }
    
    /**
     * Set block attribute value
     * 
     * Wrapper for method "setData"
     *
     * @param   string $name
     * @param   mixed $value
     * @return  Mage_Core_Block_Abstract
     */
    public function setAttribute($name, $value=null)
    {
        return $this->setData($name, $value);
    }
    
    /**
     * Set child block
     *
     * @param   string $name
     * @param   Mage_Core_Block_Abstract $block
     * @return  Mage_Core_Block_Abstract
     */
    public function setChild($name, $block)
    {
        if (is_string($block)) {
            $block = $this->getData('layout')->getBlock($block);
            if (!$block) {
                Mage::throwException(__('Invalid block name to set child %s: %s', $name, $block));
            }
        }

        if ($block->getData('is_anonymous')) {

            $suffix = $block->getData('anon_suffix');
            if (empty($suffix)) {
                $suffix = 'child'.sizeof($this->_children);
            }
            $blockName = $this->getData('name').'.'.$suffix;

            if ($this->getData('layout')) {
                $this->getData('layout')->unsetBlock($block->getData('name'));
                $this->getData('layout')->setBlock($blockName, $block);
            }

            $block->setData($blockName);
            $block->setData('is_anonymous', false);

            if (empty($name)) {
               $name = $blockName;
            }
        }

        $block->setParentBlock($this);
        $block->setBlockAlias($name);

        $block->setData('parent', array('var'=>$name, 'block'=>$this)); //deprecated
        $this->_children[$name] = $block;

        return $this;
    }
    
    /**
     * Unset child block
     *
     * @param   string $name
     * @return  Mage_Core_Block_Abstract
     */
    public function unsetChild($name)
    {
        if (isset($this->_children[$name])) {
            unset($this->_children[$name]);
        }
        
        $list = $this->getData('sorted_children_list');
        if (!empty($list)) {
            $key = array_search($name, $list);
            if (!empty($key)) {
                unset($list[$key]);
                $this->setData('sorted_children_list', $list);
            }
        }
        
        return $this;
    }
    
    /**
     * Unset all children blocks
     *
     * @return Mage_Core_Block_Abstract
     */
    public function unsetChildren()
    {
        $this->_children = array();
        $this->setData('sorted_children_list', array());
        return $this;
    }

    /**
     * Retrieve child block by name
     *
     * @param  string $name
     * @return mixed
     */
    public function getChild($name='')
    {
        if (''===$name) {
            return $this->_children;
        } elseif (isset($this->_children[$name])) {
            return $this->_children[$name];
        }
        return false;
    }
    
    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    public function getChildHtml($name='', $useCache=true)
    {
        if ('' === $name) {
            $children = $this->getChild();
            
            $out = '';
            foreach ($children as $child) {
                $out .= $this->_getChildHtml($child->getBlockAlias(), $useCache);
            }
            return $out;
        } else {
            return $this->_getChildHtml($name, $useCache);
        }
    }
    
    /**
     * Retrieve child block HTML
     *
     * @param   string $name
     * @param   boolean $useCache
     * @return  string
     */
    protected function _getChildHtml($name, $useCache=true)
    {
        if ($useCache && isset($this->_childrenHtmlCache[$name])) {
            return $this->_childrenHtmlCache[$name];
        }

        $child = $this->getChild($name);
        if (!$child) {
            $html = '';
        } else {
            $this->_beforeChildToHtml($name, $child);
            $html = $child->toHtml();
        }

        $this->_childrenHtmlCache[$name] = $html;
        return $html;
    }
    
    /**
     * Prepare child block before generate html
     *
     * @param   string $name
     * @param   Mage_Core_Block_Abstract $child
     */
    protected function _beforeChildToHtml($name, $child) {}
    
    /**
     * Retrieve block html
     *
     * @param   string $name
     * @return  string
     */
    public function getBlockHtml($name)
    {
        if (!($layout = $this->getLayout())
            && !($layout = Mage::registry('action')->getLayout())) {
            return '';
        }
        if (!($block = $layout->getBlock($name))) {
            return '';
        }
        return $block->toHtml();
    }

    /**
     * Insert child block
     *
     * @param   Mage_Core_Block_Abstract $block
     * @param   string $siblingName
     * @param   boolean $after
     * @return  object $this
     */
    function insert($block, $siblingName='', $after=false)
    {
        if ($block->getData('is_anonymous')) {
            $this->setChild('', $block);
            $name = $block->getData('name');
        } 
        else {
            $name = $block->getData('name');
            $this->setChild($name, $block);
        }

        $list = $this->getData('sorted_children_list');
        if (empty($list)) {
            $list = array();
        }

        if (''===$siblingName) {
            if ($after) {
                array_push($list, $name);
            } 
            else {
                array_unshift($list, $name);
            }
        } else {
            $key = array_search($siblingName, $list);
            if (false!==$key) {
                if ($after) {
                  $key++;
                }
                array_splice($list, $key, 0, $name);
            }
        }

        $this->setData('sorted_children_list', $list);

        return $this;
    }
    
    /**
     * Append child block
     *
     * @param   Mage_Core_Block_Abstract $block
     * @return  Mage_Core_Block_Abstract
     */
    function append($block)
    {
        $this->insert($block, '', true);
        return $this;
    }

    /**
     * Before rendering html
     *
     * If returns false html is rendered empty
     *
     * @return boolean
     */
    protected function _beforeToHtml()
    {
    	if (Mage::getStoreConfig('advanced/modules_disable_output/'.$this->getModuleName())) {
    		return false;
    	}
        return true;
    }
    
    
    public function toHtml()
    {

    }

    public function getUrl($params='', $params2=array())
    {
        return Mage::registry('controller')->getUrl($params, $params2);
    }

    public function getSkinUrl($file=null, array $params=array())
    {
    	return Mage::getDesign()->getSkinUrl($file, $params);
    }

    public function getCacheKey()
    {
        if (!$this->hasData('cache_key')) {
            $this->setCacheKey($this->getName());
        }
        return $this->getData('cache_key');
    }

    public function getCacheTags()
    {
        if (!$this->hasData('cache_tags')) {
            return array();
        }
        return $this->getData('cache_tags');
    }

    public function getCacheLifetime()
    {
        if (!$this->hasData('cache_lifetime')) {
            return null;
        }
        return $this->getData('cache_lifetime');
    }

    protected function _loadCache()
    {
        if (is_null($this->getCacheLifetime())) {
            return false;
        }
        return $this->getLayout()->getBlockCache()->load($this->getCacheKey());
    }

    protected function _saveCache($data)
    {
        if (is_null($this->getCacheLifetime())) {
            return false;
        }
        $this->getLayout()->getBlockCache()->save($data, $this->getCacheKey(), $this->getCacheTags(), $this->getCacheLifetime());
        return $this;
    }

    /**
     * Retrieve messages block
     *
     * @return Mage_Core_Block_Messages
     */
    public function getMessagesBlock()
    {
        if (is_null($this->_messagesBlock)) {
            return $this->getLayout()->getMessagesBlock();
        }
        return $this->_messagesBlock;
    }

    /**
     * Set messages block
     *
     * @param   Mage_Core_Block_Messages $block
     * @return  Mage_Core_Block_Abstract
     */
    public function setMessagesBlock(Mage_Core_Block_Messages $block)
    {
        $this->_messagesBlock = $block;
        return $this;
    }

    public function getHelper($type)
    {
        return $this->getLayout()->getHelper($type);
    }

    public function formatDate($date, $format='short', $showTime=false)
    {
        if ('short'!==$format && 'medium'!==$format && 'long'!==$format) {
            return $date;
        }
        return strftime(Mage::getStoreConfig('general/local/date'.($showTime?'time':'').'_format_'.$format), strtotime($date));
    }
    
    /**
     * Retrieve module name of block
     *
     * @return string
     */
    public function getModuleName()
    {
        $module = $this->getData('module_name');
        if (is_null($module)) {
            $class = get_class($this);
            $module = substr($class, 0, strpos($class, '_Block'));
            $this->setData('module_name', $module);
        }
        return $module;
    }
    
    /**
     * Translate block sentence
     *
     * @return string
     */
    public function __()
    {
        $args = func_get_args();
        $expr = new Mage_Core_Model_Translate_Expr(array_shift($args), $this->getModuleName());
        array_unshift($args, $expr);
        return Mage::getSingleton('core/translate')->translate($args);
    }
}// Class Mage_Home_ContentBlock END