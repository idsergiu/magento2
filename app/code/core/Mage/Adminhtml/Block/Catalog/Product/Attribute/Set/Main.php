<?php
/**
 * @package     Mage
 * @subpackage  Admihtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Block_Catalog_Product_Attribute_Set_Main extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        $this->setTemplate('catalog/product/attribute/set/main.phtml');
    }

    protected function _initChildren()
    {
        $setId = $this->_getSetId();

        $this->setChild('group_tree',
            $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_main_tree_group')
        );

        $this->setChild('edit_set_form',
            $this->getLayout()->createBlock('adminhtml/catalog_product_attribute_set_main_formset')
        );

        $this->setChild('delete_group_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                                                        ->setData(array(
                                                            'label'     => __('Delete Selected Group'),
                                                            'onclick'   => 'editSet.submit();',
									'class' => 'delete'
                                                        ))
        );

        $this->setChild('add_group_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                                                        ->setData(array(
                                                            'label'     => __('Add New'),
                                                            'onclick'   => 'editSet.addGroup();',
									'class' => 'add'
                                                        ))
        );

        $this->setChild('backButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Back'),
                    'onclick'   => 'window.location.href=\''.Mage::getUrl('*/*/').'\'',
									'class' => 'back'
                ))
        );

        $this->setChild('resetButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Reset'),
                    'onclick'   => 'window.location.reload()'
                ))
        );

        $this->setChild('saveButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Save Product Set'),
                    'onclick'   => 'editSet.save();',
									'class' => 'save'
                ))
        );

        $this->setChild('deleteButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('Delete Attribute Set'),
                    'onclick'   => 'deleteConfirm(\''. __('Are you sure you want to do this?') . '\', \'' . Mage::getUrl('*/*/delete', array('id' => $setId)) . '\')',
									'class' => 'delete'
                ))
        );

        $this->setChild('renameButton',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('New Set Name'),
                    'onclick'   => 'editSet.rename()'
                ))
        );
    }

    public function getGroupTreeHtml()
    {
        return $this->getChildHtml('group_tree');
    }

    public function getSetFormHtml()
    {
        return $this->getChildHtml('edit_set_form');
    }

    protected function _getHeader()
    {
        return __("Edit Attribute Set '{$this->_getSetData()->getAttributeSetName()}'");
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_product_set/save', array('id' => $this->_getSetId()));
    }

    public function getGroupUrl()
    {
        return $this->getUrl('*/catalog_product_group/save', array('id' => $this->_getSetId()));
    }

    public function getGroupTreeJson()
    {
        $setId = $this->_getSetId();

        $groups = Mage::getModel('eav/entity_attribute_group')
                    ->getResourceCollection()
                    ->setAttributeSetFilter($setId)
                    ->load();

        $items = array();
        foreach( $groups as $node ) {
            $item = array();
            $item['text']= $node->getAttributeGroupName();
            $item['id']  = $node->getAttributeGroupId();
            $item['cls'] = 'folder';
            $item['allowDrop'] = true;
            $item['allowDrag'] = true;

            $nodeChildren = Mage::getModel('eav/entity_attribute')
                                ->getResourceCollection()
                                ->setAttributeGroupFilter($node->getAttributeGroupId())
                                ->addVisibleFilter()
                                ->load();

            if ( $nodeChildren->getSize() > 0 ) {
                $item['children'] = array();
                foreach( $nodeChildren->getItems() as $child ) {
                    $tmpArr = array();
                    $tmpArr['text'] = (( $child->getIsUserDefined() == 0 ) ? '*' : '') . $child->getAttributeName() . ' (' . $child->getAttributeCode() . ')';
                    $tmpArr['id']  = $child->getAttributeId();
                    $tmpArr['cls'] = ( $child->getIsUserDefined() == 0 ) ? 'system-leaf' : 'leaf';
                    $tmpArr['allowDrop'] = false;
                    $tmpArr['allowDrag'] = true;
                    $tmpArr['leaf'] = true;
                    $tmpArr['is_user_defined'] = $child->getIsUserDefined();
                    $tmpArr['entity_id'] = $child->getEntityAttributeId();

                    $item['children'][] = $tmpArr;
                }
            }

            $items[] = $item;
        }

        return Zend_Json::encode($items);
    }

    public function getAttributeTreeJson()
    {
        $setId = $this->_getSetId();

        $attributesIdsObj = Mage::getModel('eav/entity_attribute')
                            ->getResourceCollection()
                            ->setAttributeSetFilter($setId)
                            ->load();
        $attributesIds = array('0');
        foreach( $attributesIdsObj->getItems() as $item ) {
            $attributesIds[] = $item->getAttributeId();
        }
        $attributes = Mage::getModel('eav/entity_attribute')
                            ->getResourceCollection()
                            ->setEntityTypeFilter(Mage::registry('entityType'))
                            ->setAttributesExcludeFilter($attributesIds)
                            ->addVisibleFilter()
                            ->load();

        $items = array();
        foreach( $attributes as $node ) {
            $item = array();
            $item['text']= $node->getAttributeName() . ' (' . $node->getAttributeCode() . ')';
            $item['id']  = $node->getAttributeId();
            $item['cls'] = 'leaf';
            $item['allowDrop'] = false;
            $item['allowDrag'] = true;
            $item['leaf'] = true;
            $item['is_user_defined'] = $node->getIsUserDefined();

            $items[] = $item;
        }

        if( count($items) == 0 ) {
            $items[] = array(
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            );
        }

        return Zend_Json::encode($items);
    }

    public function getBackButtonHtml()
    {
        return $this->getChildHtml('backButton');
    }

    public function getResetButtonHtml()
    {
        return $this->getChildHtml('resetButton');
    }

    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('saveButton');
    }

    public function getDeleteButtonHtml()
    {
        return $this->getChildHtml('deleteButton');
    }

    public function getDeleteGroupButton()
    {
        return $this->getChildHtml('delete_group_button');
    }

    public function getAddGroupButton()
    {
        return $this->getChildHtml('add_group_button');
    }

    public function getRenameButton()
    {
        return $this->getChildHtml('renameButton');
    }

    protected function _getSetId()
    {
        return ( intval($this->getRequest()->getParam('id')) > 0 )
                    ? intval($this->getRequest()->getParam('id'))
                    : Mage::getModel('eav/entity_type')
                        ->load(Mage::registry('entityType'))
                        ->getDefaultAttributeSetId();
    }

    protected function _getSetData()
    {
        return Mage::getModel('eav/entity_attribute_set')->load( $this->_getSetId() );
    }
}