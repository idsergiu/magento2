<?php
/**
 * Web API adminhtml user block.
 *
 * @copyright {}
 */
class Mage_Webapi_Block_Adminhtml_User extends Mage_Backend_Block_Widget_Grid_Container
{
    /**
     * @var string
     */
    protected $_blockGroup = 'Mage_Webapi';

    /**
     * @var string
     */
    protected $_controller = 'adminhtml_user';

    /**
     * Internal constructor.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_headerText = $this->__('API Users');
        $this->_updateButton('add', 'label', $this->__('Add New API User'));
    }
}
