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
 * @category   Mage
 * @package    Mage_CustomerSegment
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Enterprise_CustomerSegment_Model_Segment_Condition_Sales extends Enterprise_CustomerSegment_Model_Segment_Condition_Combine
{
    protected $_inputType = 'select';

    /**
     * Intialize model
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setType('enterprise_customersegment/segment_condition_sales');
        $this->setValue(null);
    }

    /**
     * Return options for check new condition elemtnt
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        $conditions = array();
        $conditions[] = array('value'=>'enterprise_customersegment/segment_condition_sales_combine', 'label'=>Mage::helper('enterprise_customersegment')->__('Conditions Combination'));
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_salesamount')->getNewChildSelectOptions();
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_ordersnumber')->getNewChildSelectOptions();
        $conditions[] = Mage::getModel('enterprise_customersegment/segment_condition_sales_purchasedquantity')->getNewChildSelectOptions();

    	$conditions = array_merge_recursive(Mage_Rule_Model_Condition_Combine::getNewChildSelectOptions(), $conditions);
    	return $conditions;
    }

    public function loadValueOptions()
    {
        $options = Mage::getSingleton('sales/order_config')->getStatuses();
        $options = array_merge(array('any' => Mage::helper('enterprise_customersegment')->__('Any')), $options);
    	$this->setValueOption($options);
        return $this;
    }

    public function getValueElementType()
    {
        return 'select';
    }

    public function getExplicitApply()
    {
        return false;
    }

    public function asHtml()
    {
        return $this->getTypeElementHtml()
            . Mage::helper('enterprise_customersegment')->__('If Order Status %s %s and %s of these Conditions Match:',
                $this->getOperatorElementHtml(), $this->getValueElementHtml(), $this->getAggregatorElement()->getHtml())
            . $this->getRemoveLinkHtml();
    }
}
