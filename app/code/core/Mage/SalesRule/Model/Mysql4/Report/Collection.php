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
 * @category    Mage
 * @package     Mage_SalesRule
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales report coupons collection
 *
 * @category   Mage
 * @package    Mage_SalesRule
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_SalesRule_Model_Mysql4_Report_Collection extends Mage_Sales_Model_Mysql4_Report_Collection_Abstract
{
    /**
     * Initialize custom resource model
     *
     * @param array $parameters
     */
    public function __construct()
    {
        parent::_construct();
        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('sales/report')->init('salesrule/coupon_aggregated');
        $this->setConnection($this->getResource()->getReadConnection());
    }

    /**
     * Add selected data
     *
     * @return Mage_SalesRule_Model_Mysql4_Report_Collection
     */
    protected  function _initSelect()
    {
        if ('month' == $this->_period) {
            $period = 'DATE_FORMAT(period, \'%Y-%m\')';
        } elseif ('year' == $this->_period) {
            $period = 'EXTRACT(YEAR FROM period)';
        } else {
            $period = 'period';
        }

        $this->getSelect()->from($this->getResource()->getMainTable() , array(
            'period'            => $period,
            'coupon_code'       => 'coupon_code',
            'coupon_uses'       => 'SUM(coupon_uses)',
            'subtotal_amount'   => 'SUM(subtotal_amount)',
            'discount_amount'   => 'SUM(discount_amount)',
            'total_amount'      => 'SUM(total_amount)'
        ))
        ->group(array(
            $period,
            'coupon_code'
        ));
        return $this;
    }

}
