<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Model to create 'staging' copy of 'virtual' theme
 */
class Mage_Core_Model_Theme_Copy_VirtualToStaging
{
    /**
     * Theme model factory
     *
     * @var Mage_Core_Model_Theme_Factory
     */
    protected $_themeFactory;

    /**
     * @param Mage_Core_Model_Theme_Factory $themeFactory
     * @param array $data
     *
     */
    public function __construct(
        Mage_Core_Model_Theme_Factory $themeFactory,
        array $data = array()
    ) {
        $this->_themeFactory = $themeFactory;
    }

    /**
     * Create 'staging' theme associated with current 'virtual' theme
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    public function copy($theme)
    {
        $stagingTheme = $this->_copyPrimaryData($theme);
        $this->_copyCustomizations($theme);
        $this->_copyLayoutUpdates($theme);
        return $stagingTheme;
    }

    /**
     * Create 'staging' theme that inherits given 'virtual' theme and copies most of it's attributes
     *
     * @param Mage_Core_Model_Theme $theme
     * @return Mage_Core_Model_Theme
     */
    protected function _copyPrimaryData($theme)
    {
        $data = array(
            'parent_id'            => $theme->getId(),
            'theme_path'           => null,
            'theme_version'        => $theme->getThemeVersion(),
            'theme_title'          => sprintf('%s - Staging', $theme->getThemeTitle()),
            'preview_image'        => $theme->getPreviewImage(),
            'magento_version_from' => $theme->getMagentoVersionFrom(),
            'magento_version_to'   => $theme->getMagentoVersionTo(),
            'is_featured'          => $theme->getIsFeatured(),
            'area'                 => $theme->getArea(),
            'type'                 => Mage_Core_Model_Theme::TYPE_STAGING
        );

        $stagingTheme = $this->_themeFactory->create();
        $stagingTheme->setData($data);
        $stagingTheme->save();

        return $stagingTheme;
    }

    /**
     * Copy theme customizations
     *
     * @param Mage_Core_Model_Theme $theme
     */
    protected function _copyCustomizations($theme)
    {
        //TODO _copyCustomizations
    }

    /**
     * Copy theme customizations
     *
     * @param Mage_Core_Model_Theme $theme
     */
    protected function _copyLayoutUpdates($theme)
    {
        //TODO _copyLayoutUpdates
    }
}
