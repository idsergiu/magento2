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
 * @package     Mage_Connect
 * @subpackage  Adminhtml
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Block for release info
 *
 * @category    Mage
 * @package     Mage_Connect
 * @subpackage  Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Release
    extends Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Abstract
{

    /**
    * Constructor
    */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('connect/extension/custom/release.phtml');
    }

    /**
    * Create object of release form
    *
    * @return Mage_Connect_Block_Adminhtml_Extension_Custom_Edit_Tab_Release
    */
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('_release');

        $fieldset = $form->addFieldset('release_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Release')));

        $stabilityOptions = Mage::getModel('connect/extension')->getStabilityOptions();

        $fieldset->addField('version', 'text', array(
            'name' => 'version',
            'label' => Mage::helper('adminhtml')->__('Release Version'),
            'required' => true,
        ));

        $fieldset->addField('stability', 'select', array(
            'name' => 'stability',
            'label' => Mage::helper('adminhtml')->__('Release Stability'),
            'options' => $stabilityOptions,
        ));

        $fieldset->addField('notes', 'textarea', array(
            'name' => 'notes',
            'label' => Mage::helper('adminhtml')->__('Notes'),
            'style' => 'height:300px;',
            'required' => true,
        ));

        $form->setValues($this->getData());

        $this->setForm($form);

        return $this;
    }

}
