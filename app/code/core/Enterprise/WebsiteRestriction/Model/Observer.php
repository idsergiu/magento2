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
 * @category   Enterprise
 * @package    Enterprise_WebsiteRestriction
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Private sales and stubs observer
 *
 */
class Enterprise_WebsiteRestriction_Model_Observer
{
    /**
     * Implement website stub or private sales restriction
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictWebsite($observer)
    {
        if ((!Mage::app()->getStore()->isAdmin()) && (int)Mage::getStoreConfig('general/restriction/is_active')) {
            /* @var $controller Mage_Core_Controller_Front_Action */
            $controller = $observer->getControllerAction();
            /* @var $request Mage_Core_Controller_Request_Http */
            $request    = $controller->getRequest();
            /* @var $response Mage_Core_Controller_Response_Http */
            $response   = $controller->getResponse();
            switch ((int)Mage::getStoreConfig('general/restriction/mode')) {
                // show only landing page with 503 or 200 code
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_NONE:
                    if ($controller->getFullActionName() !== 'restriction_index_stub') {
                        $request->setModuleName('restriction')
                            ->setControllerName('index')
                            ->setActionName('stub')
                            ->setDispatched(false);
                        return;
                    }
                    if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_503 === (int)Mage::getStoreConfig('general/restriction/http_status')) {
                        $response->setHeader('HTTP/1.1','503 Service Unavailable');
                    }
                    break;

                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER:
                    // break intentionally omitted

                // show/redirect to landing page/login
                case Enterprise_WebsiteRestriction_Model_Mode::ALLOW_LOGIN:
                    if (!Mage::helper('customer')->isLoggedIn()) {
                        // see whether redirect is required and where
                        $redirectUrl = false;
                        $allowedActionNames = array_keys(Mage::getConfig()
                            ->getNode('frontend/enterprise/websiterestriction/full_action_names/login')->asArray()
                        );
                        if (Mage::helper('customer')->isRegistrationAllowed()) {
                            foreach(array_keys(Mage::getConfig()->getNode('frontend/enterprise/websiterestriction/full_action_names/register')
                                ->asArray()) as $fullActionName) {
                                $allowedActionNames[] = $fullActionName;
                            }
                        }

                        // to specified landing page
                        if (Enterprise_WebsiteRestriction_Model_Mode::HTTP_302_LANDING
                            === (int)Mage::getStoreConfig('general/restriction/http_redirect')) {
                            $allowedActionNames[] = 'cms_page_view';
                            $pageIdentifier = Mage::getStoreConfig('general/restriction/cms_page');
                            if ((!in_array($controller->getFullActionName(), $allowedActionNames))
                                || $request->getParam('page_id') === $pageIdentifier) {
                                $redirectUrl = Mage::getUrl('', array('_direct' => $pageIdentifier));
                            }
                        }
                        // to login form
                        elseif (!in_array($controller->getFullActionName(), $allowedActionNames)) {
                            $redirectUrl = Mage::getUrl('customer/account/login');
                        }

                        if ($redirectUrl) {
                            $response->setRedirect($redirectUrl);
                            $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Attempt to disallow customers registration
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictCustomersRegistration($observer)
    {
        $result = $observer->getResult();
        if ((!Mage::app()->getStore()->isAdmin()) && $result->getIsAllowed()) {
            $result->setIsAllowed((!(bool)(int)Mage::getStoreConfig('general/restriction/is_active'))
                || (Enterprise_WebsiteRestriction_Model_Mode::ALLOW_REGISTER === (int)Mage::getStoreConfig('general/restriction/mode'))
            );
        }
    }
}
