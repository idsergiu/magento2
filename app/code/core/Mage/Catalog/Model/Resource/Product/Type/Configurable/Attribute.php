<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Catalog super product attribute resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Label table name cache
     *
     * @var string
     */
    protected $_labelTable;

    /**
     * Price table name cache
     *
     * @var string
     */
    protected $_priceTable;

    /**
     * Inititalize connection and define tables
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_attribute', 'product_super_attribute_id');
        $this->_labelTable = $this->getTable('catalog_product_super_attribute_label');
        $this->_priceTable = $this->getTable('catalog_product_super_attribute_pricing');
    }

    /**
     * Retrieve Catalog Helper
     *
     * @return Mage_Catalog_Helper_Data
     */
    public function getCatalogHelper()
    {
        return Mage::helper('Mage_Catalog_Helper_Data');
    }

    /**
     * Save Custom labels for Attribute name
     *
     * @param Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute
     */
    public function saveLabel($attribute)
    {
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->from($this->_labelTable, 'value_id')
            ->where('product_super_attribute_id = :product_super_attribute_id')
            ->where('store_id = :store_id');
        $bind = array(
            'product_super_attribute_id' => (int)$attribute->getId(),
            'store_id'                   => (int)$attribute->getStoreId()
        );
        $valueId = $adapter->fetchOne($select, $bind);
        if ($valueId) {
            $adapter->update(
                $this->_labelTable,
                array(
                    'use_default' => (int) $attribute->getUseDefault(),
                    'value'       => $attribute->getLabel()
                ),
                $adapter->quoteInto('value_id = ?', (int) $valueId)
            );
        } else {
            $adapter->insert(
                $this->_labelTable,
                array(
                    'product_super_attribute_id' => (int) $attribute->getId(),
                    'store_id' => (int) $attribute->getStoreId(),
                    'use_default' => (int) $attribute->getUseDefault(),
                    'value' => $attribute->getLabel()
                )
            );
        }
        return $this;
    }

    /**
     * Save Options prices (Depends from price save scope)
     *
     * @param Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute
     * @return Mage_Catalog_Model_Resource_Product_Type_Configurable_Attribute
     */
    public function savePrices($attribute)
    {
        $write      = $this->_getWriteAdapter();
        // define website id scope
        if ($this->getCatalogHelper()->isPriceGlobal()) {
            $websiteId = 0;
        } else {
            $websiteId = (int)Mage::app()->getStore($attribute->getStoreId())->getWebsite()->getId();
        }

        $values     = $attribute->getValues();
        if (!is_array($values)) {
            $values = array();
        }

        $new = array();
        $old = array();

        // retrieve old values
        $select = $write->select()
            ->from($this->_priceTable)
            ->where('product_super_attribute_id = :product_super_attribute_id')
            ->where('website_id = :website_id');

        $bind = array(
            'product_super_attribute_id' => (int)$attribute->getId(),
            'website_id'                   => $websiteId
        );
        $rowSet = $write->fetchAll($select, $bind);
        foreach ($rowSet as $row) {
            $key = implode('-', array($row['website_id'], $row['value_index']));
            if (!isset($old[$key])) {
                $old[$key] = $row;
            } else {
                // delete invalid (duplicate row)
                $where = $write->quoteInto('value_id = ?', $row['value_id']);
                $write->delete($this->_priceTable, $where);
            }
        }

        // prepare new values
        foreach ($values as $v) {
            if (empty($v['value_index'])) {
                continue;
            }
            $key = implode('-', array($websiteId, $v['value_index']));
            $new[$key] = array(
                'value_index'   => $v['value_index'],
                'pricing_value' => $v['pricing_value'],
                'is_percent'    => $v['is_percent'],
                'website_id'    => $websiteId,
                'use_default'   => !empty($v['use_default_value']) ? true : false
            );
        }

        $insert = array();
        $update = array();
        $delete = array();

        foreach ($old as $k => $v) {
            if (!isset($new[$k])) {
                $delete[] = $v['value_id'];
            }
        }
        foreach ($new as $k => $v) {
            $needInsert = false;
            $needUpdate = false;
            $needDelete = false;

            $isGlobal   = true;
            if (!$this->getCatalogHelper()->isPriceGlobal() && $websiteId != 0) {
                $isGlobal = false;
            }

            $hasValue   = ($isGlobal && !empty($v['pricing_value']))
                || (!$isGlobal && !$v['use_default']);

            if (isset($old[$k])) {
                // data changed
                $dataChanged = ($old[$k]['is_percent'] != $v['is_percent'])
                    || ($old[$k]['pricing_value'] != $v['pricing_value']);
                if (!$hasValue) {
                    $needDelete = true;
                } else if ($dataChanged) {
                    $needUpdate = true;
                }
            } else if ($hasValue) {
                $needInsert = true;
            }

            if (!$isGlobal && empty($v['pricing_value'])) {
                $v['pricing_value'] = 0;
                $v['is_percent']    = 0;
            }

            if ($needInsert) {
                $insert[] = array(
                    'product_super_attribute_id' => $attribute->getId(),
                    'value_index'                => $v['value_index'],
                    'is_percent'                 => $v['is_percent'],
                    'pricing_value'              => $v['pricing_value'],
                    'website_id'                 => $websiteId
                );
            }
            if ($needUpdate) {
                $update[$old[$k]['value_id']] = array(
                    'is_percent'    => $v['is_percent'],
                    'pricing_value' => $v['pricing_value']
                );
            }
            if ($needDelete) {
                $delete[] = $old[$k]['value_id'];
            }
        }

        if (!empty($delete)) {
            $where = $write->quoteInto('value_id IN(?)', $delete);
            $write->delete($this->_priceTable, $where);
        }
        if (!empty($update)) {
            foreach ($update as $valueId => $bind) {
                $where = $write->quoteInto('value_id=?', $valueId);
                $write->update($this->_priceTable, $bind, $where);
            }
        }
        if (!empty($insert)) {
            $write->insertMultiple($this->_priceTable, $insert);
        }


        return $this;
    }

    /**
     * Retrieve Used in Configurable Products Attributes
     *
     * @param int $setId The specific attribute set
     * @return array
     */
    public function getUsedAttributes($setId)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()
            ->distinct(true)
            ->from(array('e' => $this->getTable('catalog_product_entity')), null)
            ->join(
                array('a' => $this->getMainTable()),
                'e.entity_id = a.product_id',
                array('attribute_id')
            )
            ->where('e.attribute_set_id = :attribute_set_id')
            ->where('e.type_id = :type_id');

        $bind = array(
            'attribute_set_id' => $setId,
            'type_id'          => Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
        );

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Get configurable attribute id by product id and attribute id
     *
     * @param Mage_Catalog_Model_Product_Type_Configurable_Attribute $attribute
     * @param $productId
     * @param $attributeId
     *
     * @return string
     */
    public function getIdByProductIdAndAttributeId($attribute, $productId, $attributeId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where('product_id = ?', $productId)
            ->where('attribute_id = ?', $attributeId);
        return $this->_getReadAdapter()->fetchOne($select);
    }

    /**
     * Delete configurable attributes by product id
     *
     * @param $productId
     */
    public function deleteAttributesByProductId($productId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), $this->getIdFieldName())
            ->where('product_id = ?', $productId);
        $this->_getWriteAdapter()->query($this->_getReadAdapter()->deleteFromSelect($select, $this->getMainTable()));
    }
}
