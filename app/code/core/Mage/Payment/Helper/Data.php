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
 * @package    Mage_Payment
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Payment module base helper
 *
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
class Mage_Payment_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_PAYMENT_METHODS = 'payment';

    /**
     * Retrieve method model object
     *
     * @param   string $code
     * @return  Mage_Payment_Model_Method_Abstract
     */
    public function getMethodInstance($code)
    {
        $key = self::XML_PATH_PAYMENT_METHODS.'/'.$code.'/model';
        $class = Mage::getStoreConfig($key);
        if (!$class) {
            Mage::throwException($this->__('Can not configuration for payment method with code: %s', $code));
        }
        return Mage::getModel($class);
    }

    /**
     * Retrieve available payment methods for store
     *
     * array structure:
     *  $index => Varien_Simplexml_Element
     *
     * @param   mixed $store
     * @return  array
     */
    public function getStoreMethods($store=null)
    {
        if (is_null($store)) {
            $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS);
        }
        elseif ($store instanceof Mage_Core_Model_Store){
            $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store->getId());
        }
        else {
            $methods = Mage::getStoreConfig(self::XML_PATH_PAYMENT_METHODS, $store);
        }

        $res = array();
        foreach ($methods as $code => $methodConfig) {
            if (!$methodConfig->is('active', 1)) {
                continue;
            }

            $methodInstance = Mage::getModel($methodConfig->getClassName());
            if (!isset($res[(int)$methodConfig->sort_order])) {
                $res[(int)$methodConfig->sort_order] = $methodInstance;
            }
            else {
                $res[] = $methodInstance;
            }
        }
        ksort($res);
        return $res;
    }

    /**
     * Retreive payment method form html
     *
     * @param   Mage_Payment_Model_Abstract $method
     * @return  Mage_Payment_Block_Form
     */
    public function getMethodFormBlock(Mage_Payment_Model_Method_Abstract $method)
    {
        $block = false;
        $blockType = $method->getFormBlockType();
        if ($this->getLayout()) {
            $block = $this->getLayout()->createBlock($blockType);
            $block->setMethod($method);
        }
        return $block;
    }

    /**
     * Retrieve payment information block
     *
     * @param   Mage_Payment_Model_Info $info
     * @return  Mage_Core_Block_Template
     */
    public function getInfoBlock(Mage_Payment_Model_Info $info)
    {
        $blockType = $info->getMethodInstance()->getInfoBlockType();
        if ($this->getLayout()) {
            $block = $this->getLayout()->createBlock($blockType);
        }
        else {
            $className = Mage::getConfig()->getBlockClassName($blockType);
            $block = new $className;
        }
        $block->setInfo($info);
        return $block;
    }
}