<?php

class Mage_Core_Model_Mysql4_Collection_Abstract extends Varien_Data_Collection_Db 
{
    /**
     * Model name
     *
     * @var string
     */
    protected $_model;
    
    /**
     * Resource model name
     *
     * @var string
     */
    protected $_resourceModel;

    /**
     * Resource instance
     *
     * @var Mage_Core_Model_Mysql4_Abstract
     */
    protected $_resource;
    
    /**
     * Store joined tables here
     *
     * @var array
     */
    protected $_joinedTables = array();
    
    /**
     * Collection constructor
     *
     * @param Mage_Core_Model_Mysql4_Abstract $resource
     */
    public function __construct($resource=null)
    {
        $this->_construct();
        
        $this->_resource = $resource;

        parent::__construct($this->getResource()->getConnection('read'));        
        
        $this->getSelect()->from(array('main_table'=>$this->getResource()->getMainTable()));
    }
    
    /**
     * Initialization here
     *
     */
    protected function _construct()
    {
        
    }
    
    /**
     * Standard resource collection initalization
     *
     * @param string $model
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected function _init($model, $resourceModel=null)
    {
        $this->setModel($model);
        if (empty($resourceModel)) {
            $resourceModel = $model;
        }
        $this->setResourceModel($resourceModel);
        return $this;
    }
    
    /**
     * Get Zend_Db_Select instance
     *
     * @return Zend_Db_Select
     */
    public function getSelect()
    {
        return $this->_sqlSelect;
    }
    
    /**
     * Set model name for collection items
     *
     * @param string $model
     * @return Mage_Core_Model_Mysql4_Collection_Abstract
     */
    public function setModel($model)
    {
        if (is_string($model)) {
            $this->_model = $model;
            $this->setItemObjectClass(Mage::getConfig()->getModelClassName($model));
        }
        return $this;
    }
    
    /**
     * Get model instance
     *
     * @param array $args
     * @return Varien_Object
     */
    public function getModelName($args=array())
    {
        return $this->_model;
    }
    
    public function setResourceModel($model)
    {
        $this->_resourceModel = $model;
    }
    
    public function getResourceModelName()
    {
        return $this->_resourceModel;
    }
        
    /**
     * Get resource instance
     *
     * @return Mage_Core_Model_Mysql4_Abstract
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            $this->_resource = Mage::getResourceModel($this->getResourceModelName());
        }
        return $this->_resource;
    }
    
    public function getTable($table)
    {
        return $this->getResource()->getTable($table);
    }
    
    public function join($table, $cond, $cols='*')
    {
        if (!isset($this->_joinedTables[$table])) {
            $this->getSelect()->join(array($table=>$this->getTable($table)), $cond, '*');
            $this->_joinedTables[$table] = true;
        }
        return $this;
    }
}