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
 * @category   Mage
 * @package    Mage_Connect
 * @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
* Class config
*
* @category   Mage
* @package    Mage_Connect
* @copyright  Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class Maged_Model_Config extends Maged_Model
{

    /**
    * Save post data to config
    *
    * @param array $p
    * @return Maged_Model_Config
    */
    public function saveConfigPost($p)
    {
        $configParams = array(
            'protocol',
            'preferred_state',
            'use_custom_permissions_mode',
            'mkdir_mode',
            'chmod_file_mode',
            'magento_root',
            'root_channel'
        );
        foreach ($configParams as $paramName){
            if (isset($p[$paramName])) {
               $this->set($paramName, $p[$paramName]);
            }
        }
        $this->save();
        return $this;
    }

    /**
    * Retrive file name
    *
    * @return string
    */
    public function getFilename()
    {
        return $this->controller()->filepath('config.ini');
    }

    /**
    * Load file
    *
    * @return Maged_Model_Config
    */
    public function load()
    {
        if (!file_exists($this->getFilename())) {
            return $this;
        }
        $rows = file($this->getFilename());
        if (!$rows) {
            return $this;
        }
        foreach ($rows as $row) {
            $arr = explode('=', $row, 2);
            if (count($arr)!==2) {
                continue;
            }
            $key = trim($arr[0]);
            $value = trim($arr[1], " \t\"'\n");
            if (!$key || $key[0]=='#' || $key[0]==';') {
                continue;
            }
            $this->set($key, $value);
        }
        return $this;
    }

    /**
    * Save file
    *
    * @return Maged_Model_Config
    */
    public function save()
    {
        if (!is_writable($this->getFilename())) {
            $this->controller()->session()
                ->addMessage('error', 'Invalid file permissions, could not save configuration.');
            return $this;
        }
        $fp = fopen($this->getFilename(), 'w');
        foreach ($this->_data as $k=>$v) {
            fwrite($fp, $k.'='.$v."\n");
        }
        fclose($fp);
        return $this;
    }

}
