<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * SalesRule Coupon Model
 *
 * @method Mage_SalesRule_Model_Resource_Coupon _getResource()
 * @method Mage_SalesRule_Model_Resource_Coupon getResource()
 * @method int getRuleId()
 * @method Mage_SalesRule_Model_Coupon setRuleId(int $value)
 * @method string getCode()
 * @method Mage_SalesRule_Model_Coupon setCode(string $value)
 * @method int getUsageLimit()
 * @method Mage_SalesRule_Model_Coupon setUsageLimit(int $value)
 * @method int getUsagePerCustomer()
 * @method Mage_SalesRule_Model_Coupon setUsagePerCustomer(int $value)
 * @method int getTimesUsed()
 * @method Mage_SalesRule_Model_Coupon setTimesUsed(int $value)
 * @method string getExpirationDate()
 * @method Mage_SalesRule_Model_Coupon setExpirationDate(string $value)
 * @method int getIsPrimary()
 * @method Mage_SalesRule_Model_Coupon setIsPrimary(int $value)
 *
 * @category    Mage
 * @package     Mage_SalesRule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Coupon extends Mage_Core_Model_Abstract
{
    /**
     * Coupon's owner rule instance
     *
     * @var Mage_SalesRule_Model_Rule
     */
    protected $_rule;

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Mage_SalesRule_Model_Resource_Coupon');
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        if (!$this->getRuleId() && $this->_rule instanceof Mage_SalesRule_Model_Rule) {
            $this->setRuleId($this->_rule->getId());
        }
        return parent::_beforeSave();
    }

    /**
     * Set rule instance
     *
     * @param  Mage_SalesRule_Model_Rule
     * @return Mage_SalesRule_Model_Coupon
     */
    public function setRule(Mage_SalesRule_Model_Rule $rule)
    {
        $this->_rule = $rule;
        return $this;
    }

    /**
     * Load primary coupon for specified rule
     *
     * @param Mage_SalesRule_Model_Rule|int Rule
     */
    public function loadPrimaryByRule($rule)
    {
        $this->getResource()->loadPrimaryByRule($this, $rule);
        return $this;
    }
}
