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
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category   Enterprise
 * @package    Enterprise_Staging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Staging store helper
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Enterprise_Staging_Helper_Store extends Mage_Core_Helper_Url
{
    const XML_PATH_STAGING_CODE_SUFFIX   = 'global/enterprise/staging/staging_store_code_suffix';

    /**
     * Cache for store rewrite suffix
     *
     * @var array
     */
    protected $_stagingCodeSuffix = null;

    /**
     * Check if a store can be shown
     *
     * @param  Enterprise_Staging_Model_Staging_Store|int $store
     * @return boolean
     */
    public function canShow($store, $where = 'frontend')
    {
        if (is_int($store)) {
            $store = Mage::getModel('enterprise_staging/staging_store')->load($store);
        }
        /* @var $store Enterprise_Staging_Model_Staging_Store */

        if (!$store->getId()) {
            return false;
        }

        return $store->isVisibleOnFrontend();
    }

    /**
     * Retrieve store code sufix
     *
     * @return string
     */
    public function getStoreCodeSuffix()
    {
        if (is_null($this->_stagingCodeSuffix)) {
            $this->_stagingCodeSuffix = (string) Mage::getConfig()->getNode(self::XML_PATH_STAGING_CODE_SUFFIX);
        }
        return $this->_stagingCodeSuffix;
    }
}
