<?php

class Mage_Sales_Model_Mysql4_Document
{
    protected $_read;
    protected $_write;
    
    protected $_documentTable;
    protected $_idField;
    protected $_attributeTable;
    
    public function __construct($data=array())
    {
        $this->_read = Mage::getSingleton('core/resource')->getConnection('sales_read');
        $this->_write = Mage::getSingleton('core/resource')->getConnection('sales_write');
        if (isset($data['docType'])) {
            $this->setDocType($data['docType']);
        }
    }
    
    public function setDocType($docType)
    {
        $this->_documentTable = Mage::getSingleton('core/resource')->getTableName('sales_resource', $docType);
        $this->_idField = $docType.'_id';
        $this->_attributeTable = Mage::getSingleton('core/resource')->getTableName('sales_resource', $docType.'_attribute');
    }
    
    /**
     * This function expects document instance as argument with:
     * - `(quote|order|invoice)_id`
     * - instance of object for each entity type in `entityTemplates`=array('self'=>...)
     *
     * @param Mage_Sales_Model_Document $document
     * @return boolean
     */
    public function load(Mage_Sales_Model_Document $document)
    {
        $rowData = $this->_read->fetchRow("select * from ".$this->_documentTable." where ".$this->_idField."=?", $document->getId());
        if (empty($rowData)) {
            $document->setId(null);
            return false;
        }
        $document->setData($rowData);
        
        $this->_loadEntities($document);
        
        return true;
    }
    
    protected function _loadEntities($document)
    {
        $sql = '';
        foreach (array('datetime', 'decimal', 'int', 'text', 'varchar') as $type) {
            if (''!==$sql) {
                $sql .= ' union ';
            }
            $sql .= "select entity_type, entity_id, attribute_code, attribute_value from ".$this->_attributeTable."_".$type." where ".$this->_idField."=".(int)$document->getId();
        }
        $attributes = $this->_read->fetchAll($sql);
        
        $entityTemplates = $document->getEntityTemplates();
        $entities = array();
        foreach ($attributes as $attr) {
            if ('self'===$attr['entity_type']) {
                $document->setData($attr['attribute_code'], $attr['attribute_value'], false);
                continue;
            }
            if (!isset($entities[$attr['entity_id']])) {
                $entities[$attr['entity_id']] = clone $entityTemplates[$attr['entity_type']];
                $entities[$attr['entity_id']]->setEntityId($attr['entity_id'], false);
                $entities[$attr['entity_id']]->setEntityType($attr['entity_type'], false);
            }
            $entities[$attr['entity_id']]->setData($attr['attribute_code'], $attr['attribute_value'], false);
        }
        foreach ($entities as $entity) {
            $document->addEntity($entity);
        }
    }
        
    public function save(Mage_Sales_Model_Document $document)
    {
        if (!$document->getId()) {
            if ($this->_write->insert($this->_documentTable, array($this->_idField=>0))) {
                $document->setId($this->_write->lastInsertId());
            }
        }
        
        $this->_saveEntities($document);

        return $this;
    }

    protected function _saveEntities(Mage_Sales_Model_Document $document)
    {
        $documentId = $document->getId();
        $this->_deleteEntities($documentId);
        
        $attributesConfig = Mage::getConfig()->getNode('global/sales/'.$document->getDocType().'/entities');
        
        $attributes = array();

        $entityId = 0;
        $entityType = 'self';
        
        $data = $document->getData();
        foreach ($data as $key=>$value) {
            if (empty($value)) {
                continue;
            }
            $attributeType = (string)$attributesConfig->descend("self/attributes/$key/type");
            if (empty($attributeType)) {
                continue;
            }
            $attributes[$attributeType][] = "("
                .(int)$documentId.", 'self', 0, "
                .$this->_write->quote($key).", "
                .$this->_write->quote($value).")";
        }
        
        $entities = $document->getEntityById();
        if (!empty($entities)) {
            foreach ($entities as $entity) {
                $entityId = $entity->getEntityId();
                $entityType = $entity->getEntityType();
                $data = $entity->getData();
                foreach ($data as $key=>$value) {
                    if (empty($value)) {
                        continue;
                    }
                    $attributeType = (string)$attributesConfig->descend("$entityType/attributes/$key/type");
                    if (empty($attributeType)) {
                        continue;
                    }
                    $attributes[$attributeType][] = "("
                        .(int)$documentId.", "
                        .$this->_write->quote($entityType).", "
                        .(int)$entityId.", "
                        .$this->_write->quote($key).", "
                        .$this->_write->quote($value).")";
                }
            }
        }
        
        if (!empty($attributes)) {
            foreach ($attributes as $type=>$rows) {
                $sql = "insert into ".$this->_attributeTable."_".$type." (".$this->_idField.", entity_type, entity_id, attribute_code, attribute_value) values ".join(', ', $rows);
                $this->_write->query($sql);
            }
        }
    }

    public function delete($documentId)
    {
        $condition = $this->_write->quoteInto($this->_idField.'=?', $documentId);
        $this->_write->delete($this->_documentTable, $condition);
    }
    
    protected function _deleteEntities($documentId)
    {
        $condition = $this->_write->quoteInto($this->_idField.'=?', $documentId);
        foreach (array('varchar', 'decimal', 'datetime', 'int', 'text') as $type) {
            $this->_write->delete($this->_attributeTable.'_'.$type, $condition);
        }
    }

}