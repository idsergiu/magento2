<?php
/**
 * Select (no/yes) grid column filter
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */
class Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Noyes extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        $options = array(
            array('value' => '', 'label' => __('All')),
            array('value' => '0', 'label' => __('Yes')),
            array('value' => '1', 'label' => __('No')),
        );
        return $options;
    }
}