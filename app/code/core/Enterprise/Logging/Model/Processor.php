<?php
/**
 * Magento Enterprise Edition
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magento Enterprise Edition License
 * that is bundled with this package in the file LICENSE_EE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magentocommerce.com/license/enterprise-edition
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
 * @category   Enterprise
 * @package    Enterprise_Logging
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://www.magentocommerce.com/license/enterprise-edition
 */
class Enterprise_Logging_Model_Processor
{
    /**
     * Logging events config
     *
     * @var object
     */
    protected $_config;

    /**
     * current event config
     *
     * @var object
     */
    protected $_eventConfig;

    /**
     * Instance of controller handler
     *
     * @var oblect
     */
    protected $_controllerActionsHandler;

    /**
     * Instance of model controller
     *
     * @var unknown_type
     */
    protected $_modelsHandler;

    /**
     * Last action name
     *
     * @var string
     */
    protected $_actionName = '';

    /**
     * Last full action name
     *
     * @var string
     */
    protected $_lastAction = '';

    /**
     * Initialization full action name
     *
     * @var string
     */
    protected $_initAction = '';

    /**
     * Flag that signal that we should skip next action
     *
     * @var bool
     */
    protected $_skipNextAction = false;

    /**
     * Temporary storage for model changes before saving to enterprise_logging_event_changes table.
     *
     * @var array
     */
    protected $_eventChanges = array();

    /**
     * Initialize configuration model, controller and model handler
     */
    public function __construct()
    {
        $this->_config = Mage::getSingleton('enterprise_logging/config');
        $this->_modelsHandler = Mage::getModel('enterprise_logging/handler_models');
        $this->_controllerActionsHandler = Mage::getModel('enterprise_logging/handler_controllers');
    }

    /**
     * preDispatch action handler
     *
     * @param string $fullActionName Full action name like 'adminhtml_catalog_product_edit'
     * @param string $actionName Action name like 'save', 'edit' etc.
     */
    public function initAction($fullActionName, $actionName)
    {
        $this->_actionName = $actionName;
        if(!$this->_initAction){
            $this->_initAction = $fullActionName;
        }
        $this->_lastAction = $fullActionName;

        $this->_skipNextAction = (!$this->_config->isActive($fullActionName)) ? true : false;
        if ($this->_skipNextAction) {
            return;
        }
        $this->_eventConfig = $this->_config->getNode($fullActionName);

        /**
         * Skip view action after save. For example on 'save and continue' click.
         * Some modules always reloading page after save. We pass comma-separated list
         * of actions into getSkipLoggingAction, it is neccesseary for such actions
         * like customer balance, when customer balance ajax tab loaded after
         * customer page.
         */
        if ($doNotLog = Mage::getSingleton('admin/session')->getSkipLoggingAction()) {
            if (is_array($doNotLog) && $key = array_search($fullActionName, $doNotLog)) {
                unset($doNotLog[$key]);
                Mage::getSingleton('admin/session')->setSkipLoggingAction($doNotLog);
                $this->_skipNextAction = true;
                return;
            }
        }
        if (isset($this->_eventConfig->skip_on_back)) {
            $addValue = array_keys($this->_eventConfig->skip_on_back->asArray());
            Mage::getSingleton('admin/session')->setSkipLoggingAction(
                array_merge($addValue, Mage::getSingleton('admin/session')->getSkipLoggingAction()));
        }
    }

    /**
     * Action model processing.
     * Get defference between data & orig_data and store in the internal modelsHandler container.
     *
     * @param object $model
     */
    public function modelChangeAfter($model, $action)
    {
        if ($this->_skipNextAction) {
            return;
        }
        $expectedModels = isset($this->_eventConfig->expected_models)
            ? $this->_eventConfig->expected_models : false;
        if (!$expectedModels || empty($expectedModels)) {
            return;
        }
        foreach ($expectedModels->children() as $expect=>$callback) {
            $className = Mage::getConfig()->getModelClassName(str_replace('__', '/', $expect));
            if ($model instanceof $className){
                $classMap = $this->_getCallbackFunction($callback, $this->_modelsHandler,
                    sprintf('model%sAfter', ucfirst($action)));
                $handler  = $classMap['handler'];
                $callback = $classMap['callback'];
                if ($handler) {
                    if ($changes = $handler->$callback($model)) {
                        $changes->setModelName($className);
                        $changes->setModelId($model->getId());
                        $this->_eventChanges[] = $changes;
                    }
                }
            }
        }
    }

    /**
     * Postdispach action handler
     *
     */
    public function logAction()
    {
        if (!$this->_initAction) {
            return;
        }
        $username = null;
        $userId   = null;
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $userId = Mage::getSingleton('admin/session')->getUser()->getId();
            $username = Mage::getSingleton('admin/session')->getUser()->getUsername();
        }
        $errors = Mage::getModel('adminhtml/session')->getMessages()->getErrors();
        $loggingEvent = Mage::getModel('enterprise_logging/event')->setData(array(
            'ip'            => Mage::helper('core/http')->getRemoteAddr(),
            'x_forwarded_ip'=> Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR'),
            'user'          => $username,
            'user_id'       => $userId,
            'is_success'    => empty($errors),
            'fullaction'    => $this->_initAction,
            'error_message' => implode("\n", array_map(create_function('$a', 'return $a->toString();'), $errors)),
        ));

        if ($this->_actionName == 'denied') {
            $_conf = $this->_config->getNode($this->_initAction);
            if (!$_conf || !$this->_config->isActive($this->_initAction)) {
                return;
            }
            $loggingEvent->setAction($_conf->action);
            $loggingEvent->setEventCode($_conf->getParent()->getParent()->getName());
            $loggingEvent->setInfo(Mage::helper('enterprise_logging')->__('Access denied'));
            $loggingEvent->setIsSuccess(0);
            $loggingEvent->save();
            return;
        }

        if ($this->_skipNextAction) {
            return;
        }

        $loggingEvent->setAction($this->_eventConfig->action);
        $loggingEvent->setEventCode($this->_eventConfig->getParent()->getParent()->getName());

        try {
            $callback = isset($this->_eventConfig->post_dispatch) ? (string)$this->_eventConfig->post_dispatch : false;
            $defaulfCallback = 'postDispatchGeneric';
            if ($this->_eventConfig->action == 'view') {
                $defaulfCallback .= 'View';
            }
            $classMap = $this->_getCallbackFunction($callback, $this->_controllerActionsHandler, $defaulfCallback);
            $handler  = $classMap['handler'];
            $callback = $classMap['callback'];
            if (!$handler) {
                return;
            }
            if ($handler->$callback($this->_eventConfig, $loggingEvent, $this)) {
                $loggingEvent->save();
                if ($eventId = $loggingEvent->getId()) {
                    foreach ($this->_eventChanges as $changes){
                        if($changes) {
                            $changes->setEventId($eventId);
                            $changes->save();
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Collected ids getter
     *
     * @return array
     */
    public function getCollectedIds()
    {
        return $this->_modelsHandler->getCollectedIds();
    }

    /**
     * Get callback function for logAction and modelChangeAfter functions
     *
     * @param string $srtCallback
     * @param oblect $defaultHandler
     * @param string $defaultFunction
     * @return array Contains two values 'handler' and 'callback' that indicate what callback function should be applied
     */
    protected function _getCallbackFunction($srtCallback, $defaultHandler, $defaultFunction)
    {
        $return = array('handler' => $defaultHandler, 'callback' => $defaultFunction);
        if (!$srtCallback) {
            return $return;
        }

        try {
            $classPath = explode('::', $srtCallback);
            if (count($classPath) == 2) {
                $return['handler'] = Mage::getSingleton(str_replace('__', '/', $classPath[0]));
                $return['callback'] = $classPath[1];
            } else {
                $return['callback'] = $classPath[0];
            }
            if (!$return['handler'] || !$return['callback'] || !method_exists($return['handler'],
                $return['callback'])) {
                Mage::throwException("Unknown callback function: {$srtCallback}");
            }
        } catch (Exception $e) {
            $return['handler'] = false;
            Mage::logException($e);
        }

        return $return;
    }
}
