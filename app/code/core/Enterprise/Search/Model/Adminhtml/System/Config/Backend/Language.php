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
 * @category    Enterprise
 * @package     Enterprise_Search
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Catalog search backend config language model
 *
 * @category   Enterprise
 * @package    Enterprise_Search
 */
class Enterprise_Search_Model_Adminhtml_System_Config_Backend_Language
    extends Mage_Core_Model_Config_Data
{
    /**
     * After change Catalog Search Use Language Fields process
     *
     * @return Enterprise_Search_Model_Adminhtml_System_Config_Backend_Language
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            Mage::getSingleton('index/indexer')->getProcessByCode('catalogsearch_fulltext')
                ->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
        }
        return $this;
    }
}
