<?php
/**
 * Admin tax class customer toolbar
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Block_Tax_Class_Customer_Toolbar_Add extends Mage_Core_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->assign('createUrl', Mage::getUrl('adminhtml/tax_class_customer/add'));
        $this->setTemplate('tax/toolbar/class/add.phtml');
    }
}