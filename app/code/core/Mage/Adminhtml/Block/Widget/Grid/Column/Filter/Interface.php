<?php
/**
 * Grid column filter interface
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Dmitriy Soroka <dmitriy@varien.com>
 */
interface Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Interface 
{
    public function getColumn();
    public function setColumn($column);
    public function getHtml();
}
