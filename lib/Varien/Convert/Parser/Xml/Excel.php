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
 * Convert excel xml parser
 *
 * @category   Varien
 * @package    Varien_Convert
 * @author     Moshe Gurvich <moshe@varien.com>
 */
class Varien_Convert_Parser_Xml_Excel extends Varien_Convert_Parser_Abstract
{
    public function parse()
    {
        $dom = new DOMDocument();
        $dom->loadXML($this->getData());

        $worksheets = $dom->getElementsByTagName('Worksheet');

        foreach ($worksheets as $worksheet) {
            $wsName = $worksheet->getAttribute('ss:Name');
            $rows = $worksheet->getElementsByTagName('Row');
            $firstRow = true;
            $fieldNames = array();
            $wsData = array();
            foreach ($rows as $row) {
                $index = 1;
                $cells = $row->getElementsByTagName('Cell');
                $rowData = array();
                foreach($cells as $cell) {
                    $value = $cell->getElementsByTagName('Data')->item(0)->nodeValue;
                    $ind = $cell->getAttribute('Index');
                    if ( $ind != null ) {
                        $index = $ind;
                    }
                    if ($firstRow) {
                        $fieldNames[$index] = $value;
                    } else {
                        $rowData[$fieldNames[$index]] = $value;
                    }
                    $index++;
                }
                $firstRow = false;
                if (!empty($rowData)) {
                    $wsData[] = $rowData;
                }
            }
            $data[$wsName] = $wsData;
        }
        $this->setData($data);

        return $this;
    }

    public function unparse()
    {
        $xml = '<'.'?xml version="1.0"?'.'><'.'?mso-application progid="Excel.Sheet"?'.'>
<Workbook xmlns:x="urn:schemas-microsoft-com:office:excel"
  xmlns="urn:schemas-microsoft-com:office:spreadsheet"
  xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';

        if ($this->getVar('single_sheet')) {
            $workbook = array('Data'=>$this->getData());
        } else {
            $workbook = $this->getData();
        }

        if (is_array($workbook)) {
            foreach ($workbook as $wsName=>$wsData) {
                if (!is_array($wsData)) {
                    continue;
                }
                $fields = $this->getGridFields($wsData);

                $xml .= '<Worksheet ss:Name="'.$wsName.'"><ss:Table>';
                $xml .= '<ss:Row>';
                foreach ($fields as $fieldName) {
                    $xml .= '<ss:Cell><Data ss:Type="String">'.$fieldName.'</Data></ss:Cell>';
                }
                $xml .= '</ss:Row>';
                foreach ($wsData as $i=>$row) {
                    if (!is_array($row)) {
                        continue;
                    }
                    $xml .= '<ss:Row>';
                    foreach ($fields as $fieldName) {
                        $data = isset($row[$fieldName]) ? $row[$fieldName] : '';
                        $xml .= '<ss:Cell><Data ss:Type="String">'.$data.'</Data></ss:Cell>';
                    }
                    $xml .= '</ss:Row>';
                }
                $xml .= '</ss:Table></Worksheet>';
            }
        }

        $xml .= '</Workbook>';

        $this->setData($xml);

        return $this;
    }
}