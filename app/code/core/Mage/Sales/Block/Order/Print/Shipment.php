<?php
/**
 * {license_notice}
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright   {copyright}
 * @license     {license_link}
 */

/**
 * Sales order details block
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Sales_Block_Order_Print_Shipment extends Mage_Sales_Block_Items_Abstract
{
    /**
     * Tracks for Shippings
     *
     * @var array
     */
    protected $_tracks = array();

     /**
     * Order shipments collection
     *
     * @var array|Mage_Sales_Model_Resource_Order_Shipment_Collection
     */
    protected $_shipmentsCollection;

    /**
     * Load all tracks and save it to local cache by shipments
     *
     * @return Mage_Sales_Block_Order_Print_Shipment
     */
    protected function _beforeToHtml()
    {
        $tracksCollection = $this->getOrder()->getTracksCollection();

        foreach($tracksCollection->getItems() as $track) {
            $shipmentId = $track->getParentId();
            $this->_tracks[$shipmentId][] = $track;
        }

        $shipment = Mage::registry('current_shipment');
        if($shipment) {
            $this->_shipmentsCollection = array($shipment);
        } else {
            $this->_shipmentsCollection = $this->getOrder()->getShipmentsCollection();
        }

        return parent::_beforeToHtml();
    }

    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle($this->__('Order # %s', $this->getOrder()->getRealOrderId()));
        }
        $this->setChild(
            'payment_info',
            $this->helper('Mage_Payment_Helper_Data')->getInfoBlock($this->getOrder()->getPayment())
        );
    }

    public function getBackUrl()
    {
        return Mage::getUrl('*/*/history');
    }

    public function getPrintUrl()
    {
        return Mage::getUrl('*/*/print');
    }

    public function getPaymentInfoHtml()
    {
        return $this->getChildHtml('payment_info');
    }

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getShipment()
    {
        return Mage::registry('current_shipment');
    }

    protected function _prepareItem(Mage_Core_Block_Abstract $renderer)
    {
        $renderer->setPrintStatus(true);

        return parent::_prepareItem($renderer);
    }

     /**
     * Retrieve order shipments collection
     *
     * @return array|Mage_Sales_Model_Resource_Order_Shipment_Collection
     */
    public function getShipmentsCollection()
    {
        return $this->_shipmentsCollection;
    }

    /**
     * Getter for order tracking numbers collection per shipment
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function getShipmentTracks($shipment)
    {
        $tracks = array();
        if (!empty($this->_tracks[$shipment->getId()])) {
            $tracks = $this->_tracks[$shipment->getId()];
        }
        return $tracks;
    }

    /**
     * Getter for shipment address by format
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return string
     */
    public function getShipmentAddressFormattedHtml($shipment)
    {
        $shippingAddress = $shipment->getShippingAddress();
        if(!($shippingAddress instanceof Mage_Sales_Model_Order_Address)) {
            return '';
        }
        return $shippingAddress->format('html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param Mage_Sales_Model_Order $order
     * @return string
     */
    public function getBillingAddressFormattedHtml($order)
    {
        $billingAddress = $order->getBillingAddress();
        if(!($billingAddress instanceof Mage_Sales_Model_Order_Address)) {
            return '';
        }
        return $billingAddress->format('html');
    }

    /**
     * Getter for billing address of order by format
     *
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @return array
     */
    public function getShipmentItems($shipment)
    {
        $res = array();
        foreach ($shipment->getItemsCollection() as $item) {
            if (!$item->getOrderItem()->getParentItem()) {
                $res[] = $item;
            }
        }
        return $res;
    }
}

