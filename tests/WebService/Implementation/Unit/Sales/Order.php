<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Customer
 *
 * @author Anton
 */
class WebService_Implementation_Unit_Sales_Order
{
    public static function orderList($xmlPath)
    {
        $orderModel = Mage::getModel('sales/order_api_v2');
        $xml = simplexml_load_file($xmlPath);
        $data = WebService_Helper_Xml::simpleXMLToArray($xml);

        $result = true;
        foreach ( $data['filters_v2'] as $filters ){
            $filterObj = new stdClass();//WebService_Helper_Data::arrayToObject($data['filters']);
            $filterObj->filter = array();

            if(count($filters['filter']['fild'])) {
                $filterObj->filter[] = WebService_Helper_Data::arrayToObject($filters['filter']['fild']);
            }

            $filterObj->complex_filter = array();
            foreach ( $filters['complex_filter']['filter'] as $complexFilter) {
                $filterObj->complex_filter[] = WebService_Helper_Data::arrayToObject($complexFilter);
            }

            $orders = $orderModel->items($filterObj);
            $obtainedResult = array();
            foreach ($orders as $id => $order){
                $obtainedResult[$id] = $order;
            }

            if(count($filters['result']) == 1){
                $res = $filters['result'];
                unset($filters['result']);
                $filters['result'][] = $res;
            }
            $keys = array_keys($filters['result'][0]);
            $obtainedResult = WebService_Helper_Data::filterArray($obtainedResult, $keys);

//            echo __FILE__ . "(" . __LINE__ . "):\n";
//            var_dump($obtainedResult);
//            echo __FILE__ . "(" . __LINE__ . "):\n";
//            var_dump($filters['result']);

            if($obtainedResult != $filters['result']){
                $result = false;
            }
        }

        return $result;
    }

}
