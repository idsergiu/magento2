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
 * @category    Enterprise
 * @package     Enterprise_PageCache
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://www.magentocommerce.com/license/enterprise-edition
 */

/**
 * Cart sidebar container
 */
class Enterprise_PageCache_Model_Container_AccountLinks extends Enterprise_PageCache_Model_Container_CustomerContainer
{
    /**
     * Get cart hash from cookies
     */
    protected function _isLogged()
    {
        return (isset($_COOKIE[Enterprise_PageCache_Model_Container_Welcome::COOKIE])) ? true : false;
    }

    /**
     * Get cache identifier
     */
    protected function _getCacheId()
    {
        return 'CONTAINER_LINKS_'.md5($this->_placeholder->getAttribute('cache_id') .
            (($this->_isLogged()) ? 'logged' : 'not_logged'));
    }

    /**
     * Generate block content
     * @param $content
     */
    public function applyInApp(&$content)
    {
        $block = Mage::app()->getLayout()->createBlock('page/template_links', 'account.links');
        $links = unserialize(base64_decode($this->_placeholder->getAttribute('links')));

        if ($links) {
            foreach ($links as $position => $linkInfo) {
                $block->addLink($linkInfo['label'], $linkInfo['url'], $linkInfo['title'], false, array(), $position,
                    $linkInfo['li_params'], $linkInfo['a_params'], $linkInfo['before_text'], $linkInfo['after_text']);
            }
        }

        $block->setTemplate($this->_placeholder->getAttribute('template'));
        $blockContent = $block->toHtml();

        $cacheId = $this->_getCacheId();
        if ($cacheId) {
            $this->_saveCache($blockContent, $cacheId);
        }
        $this->_applyToContent($content, $blockContent);
        return true;
    }
}
