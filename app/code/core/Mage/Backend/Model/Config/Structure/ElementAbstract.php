<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   {copyright}
 * @license     {license_link}
 */

abstract class Mage_Backend_Model_Config_Structure_ElementAbstract
    implements Mage_Backend_Model_Config_Structure_ElementInterface
{
    /**
     * Element data
     *
     * @var array
     */
    protected $_data;

    /**
     * Current scope
     *
     * @var string
     */
    protected $_scope;

    /**
     * Helper factory
     *
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * Application object
     *
     * @var Mage_Core_Model_App
     */
    protected $_application;

    /**
     * Authorization model
     *
     * @var Mage_Core_Model_Authorization
     */
    protected $_authorization;

    /**
     * @param Mage_Core_Model_Factory_Helper $helperFactory
     * @param Mage_Core_Model_App $application
     * @param Mage_Core_Model_Authorization $authorization
     */
    public function __construct(
        Mage_Core_Model_Factory_Helper $helperFactory,
        Mage_Core_Model_App $application,
        Mage_Core_Model_Authorization $authorization
    ) {
        $this->_helperFactory = $helperFactory;
        $this->_application = $application;
        $this->_authorization = $authorization;
    }

    /**
     * Retrieve element translation module
     *
     * @return string
     */
    protected function _getTranslationModule()
    {
        return (isset($this->_data['module']) ? $this->_data['module'] : 'Mage_Core') . '_Helper_Data';
    }

    /**
     * Translate element attribute
     *
     * @param string $code
     * @return string
     */
    protected function _getTranslatedAttribute($code)
    {
        return $this->_helperFactory->get($this->_getTranslationModule())->__($this->_data[$code]);
    }

    /**
     * Set element data
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Set current scope
     *
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->_scope = $scope;
    }

    /**
     * Retrieve element id
     *
     * @return string
     */
    public function getId()
    {
        return $this->_data['id'];
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->_getTranslatedAttribute('label');
    }

    /**
     * Retrieve element label
     *
     * @return string
     */
    public function getComment()
    {
        return $this->_getTranslatedAttribute('comment');
    }

    /**
     * Retrieve frontend model class name
     *
     * @return string
     */
    public function getFrontendModel()
    {
        return isset($this->_data['frontend_model']) ? $this->_data['frontend_model'] : '';
    }

    /**
     * Retrieve arbitrary element attribute
     *
     * @param string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return array_key_exists($key, $this->_data) ? $this->_data[$key] : null;
    }

    /**
     * Check whether section is allowed for current user
     *
     * @return bool
     */
    public function isAllowed()
    {
        return isset($this->_data['resource']) ? $this->_authorization->isAllowed($this->_data['resource']) : false;
    }

    /**
     * Check whether element should be displayed
     *
     * @return bool
     */
    public function isVisible()
    {
        if ($this->_application->isSingleStoreMode()) {
            return !(isset($this->_data['hide_in_single_store_mode']) && $this->_data['hide_in_single_store_mode']);
        }

        $result = false;
        switch ($this->_scope) {
            case Mage_Backend_Model_Config_ScopeDefiner::SCOPE_STORE:
                $result = isset($this->_data['showInWebsite']) && $this->_data['showInWebsite'];
                break;
            case Mage_Backend_Model_Config_ScopeDefiner::SCOPE_WEBSITE:
                $result = isset($this->_data['showInWebsite']) && $this->_data['showInWebsite'];
                break;
            case Mage_Backend_Model_Config_ScopeDefiner::SCOPE_DEFAULT:
                $result = isset($this->_data['showInDefault']) && $this->_data['showInDefault'];
                break;
        }

        return $result;
    }

    /**
     * Retrieve css class of a tab
     *
     * @return string
     */
    public function getClass()
    {
        return isset($tab['class']) ? $tab['class'] : '';
    }

    /**
     * Retrieve element config path
     *
     * @param string $fieldPrefix
     * @return string
     */
    public function getPath($fieldPrefix = '')
    {
        $path = isset($this->_data['path']) ? $this->_data['path'] : rand(0, 100000000);
        return $path . '/' . $fieldPrefix . $this->getId();
    }
}
