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
 * @package    Mage_Catalog
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog attribute model
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Catalog_Model_Resource_Eav_Attribute extends Mage_Eav_Model_Entity_Attribute
{
    const SCOPE_STORE   = 0;
    const SCOPE_GLOBAL  = 1;
    const SCOPE_WEBSITE = 2;

    public function isScopeGlobal()
    {
        return $this->getIsGlobal() == self::SCOPE_GLOBAL;
    }

    public function isScopeWebsite()
    {
        return $this->getIsGlobal() == self::SCOPE_WEBSITE;
    }

    public function isScopeStore()
    {
        return !$this->isScopeGlobal() && !$this->isScopeWebsite();
    }

    public function getStoreId()
    {
        if ($dataObject = $this->getDataObject()) {
            return $dataObject->getStoreId();
        }
        return $this->getData('store_id');
    }
}
