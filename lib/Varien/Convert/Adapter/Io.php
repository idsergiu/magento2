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
 * @category   Varien
 * @package    Varien_Convert
 * @copyright  Copyright (c) 2004-2007 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Convert IO adapter
 *
 * @category   Varien
 * @package    Varien_Convert
 * @author     Moshe Gurvich <moshe@varien.com>
 */
 class Varien_Convert_Adapter_Io extends Varien_Convert_Adapter_Abstract
 {
     protected $_resource;
     
     public function getResource()
     {
         if (!$this->_resource) {
            $type = $this->getVar('type', 'file');
            $className = 'Varien_Io_'.ucwords($type);
            $this->_resource = new $className();
            $this->_resource->open($this->getVars());
         }
         return $this->_resource;
     }
     
     public function load()
     {
         $data = $this->getResource()->read($this->getVar('filename'));
         $this->setData($data);
         return $this;
     }
     
     public function save()
     {
         $data = $this->getData();
         $this->getResource()->write($this->getVar('filename'), $data, 0777);
         return $this;
     }
 }