<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Design editor theme
 *
 * @method Mage_DesignEditor_Block_Adminhtml_Theme_Item setTheme(Mage_Core_Model_Theme $theme)
 * @method Mage_Core_Model_Theme getTheme()
 */
class Mage_DesignEditor_Block_Adminhtml_Theme_Item extends Mage_Backend_Block_Widget
{
    /**
     * Get theme html
     *
     * @return string
     */
    public function getThemeHtml()
    {
        $this->getChildBlock('theme')->setTheme($this->getTheme());
        return $this->getChildHtml('theme', false);
    }

    /**
     * Get launch button html
     *
     * @return string
     */
    public function getLaunchButtonHtml()
    {
        $themeId = $this->getTheme()->getId();
        /** @var $previewButton Mage_Backend_Block_Widget_Button */
        $previewButton = $this->getLayout()->createBlock('Mage_Backend_Block_Widget_Button');
        $previewButton->setData(array(
            'label'   => $this->__('Launch'),
            'data_attribute'  => array(
                'mage-init' => array(
                    'button' => array(
                        'event' => 'save',
                        'target' => '#edit_form',
                        'eventData' => array(
                            'action' => array('args' => array(
                                'theme_id' => $themeId
                            )),
                        ),
                    ),
                ),
            ),
            'class'   => 'save',
            'target'  => '_blank'
        ));

        return $previewButton->toHtml();
    }
}
