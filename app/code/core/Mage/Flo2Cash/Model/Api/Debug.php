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
 * @package    Mage_Flo2Cash
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Flo2Cash Api Debug Model
 *
 * @category    Mage
 * @package     Mage_Flo2Cash
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Flo2Cash_Model_Api_Debug extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('flo2cash/api_debug');
    }
}