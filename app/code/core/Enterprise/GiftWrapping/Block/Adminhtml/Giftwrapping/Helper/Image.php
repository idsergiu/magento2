<?php
/**
 * {license_notice}
 *
 * @category    Enterprise
 * @package     Enterprise_GiftWrapping
 * @copyright   {copyright}
 * @license     {license_link}
 */


/**
 * Gift Wrapping Image Helper
 *
 * @category   Enterprise
 * @package    Enterprise_GiftWrapping
 */
class Enterprise_GiftWrapping_Block_Adminhtml_Giftwrapping_Helper_Image extends Varien_Data_Form_Element_Image
{
    /**
     * Get gift wrapping image url
     *
     * @return string|boolean
     */
    protected function _getUrl()
    {
        $url = false;
        if ($this->getValue()) {
            $url = $this->getForm()->getDataObject()->getImageUrl();
        }
        return $url;
    }

    /**
     * Get default field name
     *
     * @return string
     */
    public function getDefaultName()
    {
        $name = $this->getData('name');
        $suffix = $this->getForm()->getFieldNameSuffix();
        if ($suffix) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

}
