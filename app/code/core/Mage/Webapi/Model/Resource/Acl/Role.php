<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Webapi
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Web API ACL role resource
 *
 * @category    Mage
 * @package     Mage_Webapi
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Webapi_Model_Resource_Acl_Role extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('webapi_role', 'role_id');
    }

    /**
     * Get roles list for selects
     *
     * @return array
     */
    public function getRolesList()
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), array($this->getIdFieldName(), 'role_name'))
            ->order('role_name');
        return $adapter->fetchPairs($select);
    }

    /**
     * Get all roles ids
     *
     * @return array
     */
    public function getRolesIds()
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select()->from($this->getMainTable(), array($this->getIdFieldName()));
        return $adapter->fetchCol($select);
    }
}
