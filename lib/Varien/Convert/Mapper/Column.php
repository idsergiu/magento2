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
 * Convert column mapper
 *
 * @category   Varien
 * @package    Varien_Convert
 * @author     Moshe Gurvich <moshe@varien.com>
 */
class Varien_Convert_Mapper_Column extends Varien_Convert_Mapper_Abstract
{
	public function map()
    {
        $data = $this->getData();
        $this->validateDataGrid($data);
        $mappedData = array();
        foreach ($data as $i=>$row) {
            $newRow = array();
            foreach ($row as $field=>$value) {
                $newRow[$this->getVar($field, $field)] = $value;
            }
            $mappedData[$i] = $newRow;
        }
        $this->setData($mappedData);
        return $this;
    }
}