<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */


/**
 * Cms Hierarchy Pages Node Model
 *
 * @category   Enterprise
 * @package    Enterprise_Cms
 */
class Enterprise_Cms_Model_Hierarchy_Node extends Mage_Core_Model_Abstract
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('enterprise_cms/hierarchy_node');
    }

    /**
     * Retrieve Resource instance wrapper
     *
     * @return Enterprise_Cms_Model_Mysql4_Hierarchy_Node
     */
    protected function _getResource()
    {
        return parent::_getResource();
    }

    /**
     * Validate Unique Hierarchy Identifier
     *
     * @throws Mage_Core_Exception
     * @return bool
     */
    public function validateHierarchyIdentifier()
    {
        $this->loadPageData();
        $identifier = $this->getIdentifier();
        if (empty($identifier)) {
            Mage::throwException(Mage::helper('enterprise_cms')->__('Please enter a valid Identifier'));
        }

        if (!$this->_getResource()->validateHierarchyIdentifier($this->getIdentifier())) {
            Mage::throwException(Mage::helper('enterprise_cms')->__('Hierarchy with same Identifier already exists'));
        }

        return true;
    }

    /**
     * Collect and save tree
     *
     * @param array $data
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function collectTree($data, $remove)
    {
        if (!is_array($data)) {
            return $this;
        }

        $nodes = array();
        foreach ($data as $v) {
            $parentNodeId = empty($v['parent_node_id']) ? 0 : $v['parent_node_id'];
            $pageId = empty($v['page_id']) ? null : intval($v['page_id']);

            $_node = array(
                'node_id'            => strpos($v['node_id'], '_') === 0 ? null : intval($v['node_id']),
                'page_id'            => $pageId,
                'label'              => !$pageId ? $v['label'] : null,
                'identifier'         => !$pageId ? $v['identifier'] : null,
                'level'              => intval($v['level']),
                'sort_order'          => intval($v['sort_order']),
                'request_url'        => $v['identifier']
            );

            $nodes[$parentNodeId][$v['node_id']] = Mage::helper('enterprise_cms/hierarchy')->copyMetaData($v, $_node);
        }

        $this->_getResource()->beginTransaction();
        try {
            $this->_collectTree($nodes, $this->getId(), $this->getRequestUrl(), $this->getId(), 0);
            $this->_getResource()->dropNodes($remove);

            $this->_getResource()->commit();
        } catch (Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Recursive save nodes
     *
     * @param array $nodes
     * @param int $parentNodeId
     * @param string $path
     * @param int $level
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    protected function _collectTree(array $nodes, $parentNodeId, $path = '', $xpath = '', $level = 0)
    {
        if (!isset($nodes[$level])) {
            return $this;
        }
        foreach ($nodes[$level] as $k => $v) {
            $v['parent_node_id'] = $parentNodeId;
            if ($path != '') {
                $v['request_url'] = $path . '/' . $v['request_url'];
            } else {
                $v['request_url'] = $v['request_url'];
            }

            if ($xpath != '') {
                $v['xpath'] = $xpath . '/';
            } else {
                $v['xpath'] = '';
            }

            // create new or modify exists node using current instance of object
            $this->setData($v)->save();

            if (isset($nodes[$k])) {
                $this->_collectTree($nodes, $this->getId(), $this->getRequestUrl(), $this->getXpath(), $k);
            }
        }
        return $this;
    }

    /**
     * Retrieve Node or Page identifier
     *
     * @return string
     */
    public function getIdentifier()
    {
        if (is_null($this->_getData('identifier'))) {
            return $this->_getData('page_identifier');
        }
        return $this->_getData('identifier');
    }

    /**
     * Is Node used original Page Identifier
     *
     * @return bool
     */
    public function isUseDefaultIdentifier()
    {
        return is_null($this->_getData('identifier'));
    }

    /**
     * Retrieve Node label or Page title
     *
     * @return string
     */
    public function getLabel()
    {
        if (is_null($this->_getData('label'))) {
            return $this->_getData('page_title');
        }
        return $this->_getData('label');
    }

    /**
     * Is Node used original Page Label
     *
     * @return bool
     */
    public function isUseDefaultLabel()
    {
        return is_null($this->_getData('label'));
    }

    /**
     * Load node by Request Url
     *
     * @param string $url
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function loadByRequestUrl($url)
    {
        $this->_getResource()->loadByRequestUrl($this, $url);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Retrieve first child node
     *
     * @param int $parentNodeId
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function loadFirstChildByParent($parentNodeId)
    {
        $this->_getResource()->loadFirstChildByParent($this, $parentNodeId);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Update rewrite for page (if identifier changed)
     *
     * @param Mage_Cms_Model_Page $page
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function updateRewriteUrls(Mage_Cms_Model_Page $page)
    {
        $xpaths = $this->_getResource()->getTreeXpathsByPage($page->getId());
        foreach ($xpaths as $xpath) {
            $this->_getResource()->updateRequestUrlsForTreeByXpath($xpath);
        }
        return $this;
    }

    /**
     * Check identifier
     *
     * If a CMS Page belongs to a tree (binded to a tree node), it should not be accessed standalone
     * only by URL that identifies it in a hierarchy.
     *
     * Return true if a page binded to a tree node
     *
     * @param string $identifier
     * @return bool
     */
    public function checkIdentifier($identifier)
    {
        return $this->_getResource()->checkIdentifier($identifier);
    }

    /**
     * Retrieve Chapter Node
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getChapterNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'chapter');
    }

    /**
     * Retrieve Section Node
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getSectionNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'section');
    }

    /**
     * Retrieve Next Node
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getNextNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'next');
    }

    /**
     * Retrieve Previous Node
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getPreviousNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'previous');
    }

    /**
     * Retrieve First Node in current level
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getFirstNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'first');
    }

    /**
     * Retrieve Last Node in current level
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function getLastNode()
    {
        return Mage::getModel('enterprise_cms/hierarchy_node')
            ->loadByNodeType($this, 'last');
    }

    /**
     * Load Node by Parent node and Type
     * Allowed types:
     *  - chapter       parent node chapter
     *  - section       parent node section
     *  - first         first node in current parent node level
     *  - last          last node in current parent node level
     *  - next          next node (only in current parent node level)
     *  - previous      previous node (only in current parent node level)
     *
     * @param Enterprise_Cms_Model_Hierarchy_Node $node The parent node
     * @param string $type
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function loadByNodeType(Enterprise_Cms_Model_Hierarchy_Node $node, $type)
    {
        if (!$node->getId()) {
            return $this;
        }
        $this->_getResource()->loadByNodeType($this, $node, $type);
        $this->_afterLoad();
        $this->setOrigData();
        return $this;
    }

    /**
     * Retrieve Page URL
     *
     * @param mixed $store
     * @return string
     */
    public function getUrl($store = null)
    {
        return Mage::app()->getStore($store)->getUrl('', array(
            '_direct' => $this->getRequestUrl()
        ));
    }

    /**
     * Retrieve Tree Slice
     * 2 level array
     *
     * @param int $up
     * @param int $down
     * @return array
     */
    public function getTreeSlice($up = 0, $down = 0)
    {
        return $this->_getResource()->getTreeSlice($this, $up, $down);
    }

    /**
     * Retrieve Parent node children
     *
     * @return array
     */
    public function getParentNodeChildren()
    {
        return $this->_getResource()->getParentNodeChildren($this);
    }

    /**
     * Load page data for model if defined page id end undefined page data
     *
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function loadPageData()
    {
        if ($this->getPageId() && !$this->getPageIdentifier()) {
            $this->_getResource()->loadPageData($this);
        }

        return $this;
    }

    /**
     * Appending passed page as child node for specified nodes.
     *
     * @param Mage_Cms_Model_Page $page
     * @param array $nodeIds
     * @return Enterprise_Cms_Model_Hierarchy_Node
     */
    public function appendPageToNodes($page, $nodeIds)
    {
        $parentNodes = $this->getCollection()
            ->joinPageExistsNodeInfo($page)
            ->applyPageExistsOrNodeIdFilter($nodeIds, $page)
            ->addLastChildSortOrderColumn();

        $pageData = array(
            'page_id' => $page->getId(),
            'identifier' => null,
            'label' => null
        );

        $removeFromNodes = array();

        foreach ($parentNodes as $node) {
            /* @var $node Enterprise_Cms_Model_Hierarchy_Node */
            if (in_array($node->getId(), $nodeIds)) {
                if ($node->getPageExists()) {
                    continue;
                } else {
                    $node->addData($pageData)
                        ->setParentNodeId($node->getId())
                        ->unsetData($this->getIdFieldName())
                        ->setLevel($node->getLevel() + 1)
                        ->setSortOrder($node->getLastChildSortOrder() + 1)
                        ->setRequestUrl($node->getRequestUrl() . '/' . $page->getIdentifier())
                        ->setXpath($node->getXpath() . '/')
                        ->save();
                }
            } else {
                $removeFromNodes[] = $node->getId();
            }
        }

        if (!empty($removeFromNodes)) {
            $this->_getResource()->removePageFromNodes($page->getId(), $removeFromNodes);
        }

        return $this;
    }

    public function getTreeMetaData()
    {
        if (is_null($this->_treeMetaData)) {
            $this->_treeMetaData = $this->_getResource()->getTreeMetaData();
        }

        return $this->_treeMetaData;
    }
}
