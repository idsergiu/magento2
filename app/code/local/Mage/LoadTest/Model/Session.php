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
 * @category   Mage
 * @package    Mage_LoadTest
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * LoadTest Session model
 *
 * @category   Mage
 * @package    Mage_LoadTest
 * @author     Victor Tihonchuk <victor@varien.com>
 */

class Mage_LoadTest_Model_Session extends Mage_Core_Model_Session_Abstract
{
    /**
     * XML path to auth key
     *
     */
    const XML_PATH_KEY      = 'dev/loadtest/key';

    /**
     * XML path to module status
     *
     */
    const XML_PATH_STATUS   = 'dev/loadtest/status';

    /**
     * SimpleXml
     *
     * @var Varien_Simplexml_Element
     */
    protected $_xml;

    /**
     * SimpleXml request node
     *
     * @var Varien_Simplexml_Element
     */
    protected $_xml_request;

    /**
     * SimpleXml response node
     *
     * @var Varien_Simplexml_Element
     */
    protected $_xml_response;

    protected $_sql = array();
    protected $_sql_total_time = 0;

    protected $_timers = array();

    /**
     * Init Session model
     *
     */
    public function __construct()
    {
        $this->init('loadtest');
        Mage::dispatchEvent('loadtest_session_init', array('loadtest_session'=>$this));

        $this->setCountLoadTime(0);
        if ($this->isEnabled()) {
            $this->_xml = new Varien_Simplexml_Element('<?xml version="1.0"?><loadtest></loadtest>');
            $this->_xml_request = $this->_xml->addChild('request');
            $this->_xml_response = $this->_xml->addChild('response');
            $this->_getRequestUrl();
            $this->_getTotalData();
            $this->_getCacheSettings();

            $this->_timers['page'] = null;
            $this->_timers['block'] = array();
            $this->_timers['sql'] = array();
        }
    }

    /**
     * Return status module
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) Mage::getStoreConfig(self::XML_PATH_STATUS);
    }

    public function isAcceptedController($controllerName)
    {
        $controllers = array(
            'Mage_LoadTest_IndexController',
            'Mage_LoadTest_RenderController',
            'Mage_LoadTest_DeleteController',
        );

        return !in_array($controllerName, $controllers);
    }

    public function spiderXml()
    {
        $this->_xml = new Varien_Simplexml_Element('<?xml version="1.0"?><loadtest></loadtest>');
        $this->_xml->addChild('status', intval($this->isEnabled()));
        $this->_xml->addChild('logged_in', intval($this->isLoggedIn()));
    }

    public function login($key)
    {
        $this->setKey($key);
        return $this->isLoggedIn();
    }

    /**
     * Return authorization status
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return true;
        return $this->getKey() == Mage::getStoreConfig(self::XML_PATH_KEY);
    }

    public function getIsClear($area)
    {
        return $this->isEnabled() && $this->isLoggedIn() && $area == 'frontend';
    }

    public function pageStart()
    {
        $this->_timers['page'] = microtime(true);
    }

    public function pageStop()
    {
        if ($this->_timers['page']) {
            if (!$this->_xml_response->page) {
                $this->_xml_response->addChild('page');
            }
            $this->_xml_response->page->addChild('total_time', microtime(true) - $this->_timers['page']);
        }
    }

    public function sqlStart(string $sql)
    {
        if (isset($this->_sql[$sql])) {
            $this->_sql[$sql] ++;
        }
        else {
            $this->_sql[$sql] = 1;
            $this->_timers['sql'][$sql] = array();
        }

        $this->_timers['sql'][$sql][$this->_sql[$sql]] = array(microtime(true), microtime(true));
    }

    public function sqlStop(string $sql)
    {
        if (isset($this->_sql[$sql]) && isset($this->_timers['sql'][$sql][$this->_sql[$sql]])) {
            $this->_timers['sql'][$sql][$this->_sql[$sql]][1] = microtime(true);
            $this->_sql_total_time += $this->_timers['sql'][$sql][$this->_sql[$sql]][1] - $this->_timers['sql'][$sql][$this->_sql[$sql]][0];
        }
    }

    public function prepareXmlResponse($content)
    {
        Mage::app()->getResponse()->setHeader('Content-Type', 'text/xml');
        Mage::app()->getResponse()->setBody($content);
    }

    public function prepareOutputData()
    {
        /**
         * Prepare SQL data
         */

        $sqlNode = $this->_xml_response->addChild('sql');
        $sqlNode->addChild('total_time', $this->_sql_total_time);
        $queriesNode = $sqlNode->addChild('queries');

        arsort($this->_sql);

        foreach ($this->_sql as $sql => $count) {
            $queryNode = $queriesNode->addChild('query');
            $queryNode->addChild('string', $sql)
                ->addAttribute('count', $count);
            $i = 0;
            foreach ($this->_timers['sql'][$sql] as $timer) {
                $queryNode->addChild('time', $timer[1] - $timer[0])
                    ->addAttribute('id', $i);
                $i ++;
            }
        }
    }

    public function getResult()
    {
        return $this->_xml->asXML();
    }

    /**
     * Get Total Data counts
     *
     */
    protected function _getTotalData()
    {
        $loadTime  = $this->getCountLoadTime();
        $startTime = microtime(true);

        $categoriesCount = Mage::getModel('catalog/category')
            ->getCollection()
            ->getSize();

        $productsCount = Mage::getModel('catalog/product')
            ->getCollection()
            ->getSize();

        $customersCount = Mage::getModel('customer/customer')
            ->getCollection()
            ->getSize();

        $ordersCount = Mage::getModel('sales/order')
            ->getCollection()
            ->getSize();

        $tagsCount = Mage::getModel('tag/tag')
            ->getCollection()
            ->getSize();

        $reviewsCount = Mage::getModel('review/review')
            ->getCollection()
            ->getSize();

        $totalCountsNode = $this->_xml_response->addChild('total_data_counts');
        $totalCountsNode->addChild('products', $productsCount);
        $totalCountsNode->addChild('categories', $categoriesCount);
        $totalCountsNode->addChild('customers', $customersCount);
        $totalCountsNode->addChild('orders', $ordersCount);
        $totalCountsNode->addChild('tags', $tagsCount);
        $totalCountsNode->addChild('reviews', $reviewsCount);

        $this->setCountLoadTime($loadTime + (microtime(true) - $startTime));
    }

    /**
     * Get Request URL
     *
     */
    protected function _getRequestUrl()
    {
        $loadTime  = $this->getCountLoadTime();
        $startTime = microtime(true);

        $requestUrl = Mage::app()->getRequest()->getOriginalPathInfo();
        $this->_xml_request->addChild('request_url', $requestUrl);

        $this->setCountLoadTime($loadTime + (microtime(true) - $startTime));
    }

    /**
     * Get Cache settings (for each type of caches)
     *
     */
    protected function _getCacheSettings()
    {
        $loadTime  = $this->getCountLoadTime();
        $startTime = microtime(true);

        $cacheTypes = array(
            'config'     => Mage::helper('adminhtml')->__('Configuration'),
            'layout'     => Mage::helper('adminhtml')->__('Layouts'),
            'block_html' => Mage::helper('adminhtml')->__('Blocks HTML output'),
            'eav'        => Mage::helper('adminhtml')->__('EAV types and attributes'),
            'translate'  => Mage::helper('adminhtml')->__('Translations'),
            'pear'       => Mage::helper('adminhtml')->__('PEAR Channels and Packages'),
        );

        $this->setCacheTypes($cacheTypes);

        $cacheNode = $this->_xml_response->addChild('cache');

        foreach ($cacheTypes as $type => $label) {
            $cacheNode->addChild($type, intval(Mage::app()->useCache($type)));
        }

        $this->setCountLoadTime($loadTime + (microtime(true) - $startTime));
    }
}