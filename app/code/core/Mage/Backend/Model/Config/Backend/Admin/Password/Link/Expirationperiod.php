<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Admin Reset Password Link Expiration period backend model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Backend_Admin_Password_Link_Expirationperiod
    extends Mage_Core_Model_Config_Data
{
    /**
     * Validate expiration period value before saving
     *
     * @return Mage_Backend_Model_Config_Backend_Admin_Password_Link_Expirationperiod
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $resetPasswordLinkExpirationPeriod = (int)$this->getValue();

        if ($resetPasswordLinkExpirationPeriod < 1) {
            $resetPasswordLinkExpirationPeriod = (int)$this->getOldValue();
        }
        $this->setValue((string)$resetPasswordLinkExpirationPeriod);
        return $this;
    }
}
