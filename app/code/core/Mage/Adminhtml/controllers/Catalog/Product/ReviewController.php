<?php
/**
 * Reviews admin controller
 *
 * @package     Mage
 * @subpackage  Adminhtml
 * @copyright   Varien (c) 2007 (http://www.varien.com)
 * @license     http://www.opensource.org/licenses/osl-3.0.php
 * @author      Alexander Stadnitski <alexander@varien.com>
 */

class Mage_Adminhtml_Catalog_Product_ReviewController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_main'));

        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/review');

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_edit'));

        $this->renderLayout();
    }

    public function newAction()
    {
        $this->loadLayout('baseframe');
        $this->_setActiveMenu('catalog/review');

        $this->getLayout()->getBlock('root')->setCanLoadExtJs(true);

        $this->_addContent($this->getLayout()->createBlock('adminhtml/review_add'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        $url = $this->getRequest()->getServer('HTTP_REFERER', Mage::getBaseUrl());

        $reviewId = $this->getRequest()->getParam('id', false);
        if ($data = $this->getRequest()->getPost()) {
            $review = Mage::getModel('review/review')->setData($data);
            try {
                $review->setId($reviewId)
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $voteId=>$optionId) {
                	Mage::getModel('rating/rating')
                	   ->setVoteId($voteId)
                	   ->setReviewId($review->getId())
                	   ->updateOptionVote($optionId);
                }

                $review->aggregate();

                Mage::getSingleton('adminhtml/session')->addSuccess(__('Review successfully saved.'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                return;
            } catch (Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect($url);
    }

    public function deleteAction()
    {
        $url = $this->getRequest()->getServer('HTTP_REFERER', Mage::getBaseUrl());

        $reviewId = $this->getRequest()->getParam('id', false);

        try {
            Mage::getModel('review/review')->setId($reviewId)
                ->delete();

            Mage::getSingleton('adminhtml/session')->addSuccess(__('Review successfully deleted.'));
            $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
            return;
        } catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->getResponse()->setRedirect($url);
    }

    public function productGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/review_product_grid')->toHtml());
    }

    public function reviewGridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('adminhtml/review_grid')->toHtml());
    }

    public function jsonProductInfoAction()
    {
        $response = new Varien_Object();
        $id = $this->getRequest()->getParam('id');
        if( intval($id) > 0 ) {
            $product = Mage::getModel('catalog/product')
                ->load($id);

            $response->setId($id);
            $response->addData($product->getData());
            $response->setError(0);
        } else {
            $response->setError(1);
            $response->setMessage(__('Unable to get product id.'));
        }
        $this->getResponse()->setBody($response->toJSON());
    }

    public function postAction()
    {
        $productId = $this->getRequest()->getParam('product_id', false);
        if ($data = $this->getRequest()->getPost()) {
            $review = Mage::getModel('review/review')->setData($data);
            $product = Mage::getModel('catalog/product')
                ->load($productId);

            try {
                $review->setEntityId(1) // product
                    ->setEntityPkValue($productId)
                    ->setStoreId($product->getStoreId())
                    ->setStatusId($data['status_id'])
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                foreach ($arrRatingId as $ratingId=>$optionId) {
                	Mage::getModel('rating/rating')
                	   ->setRatingId($ratingId)
                	   ->setReviewId($review->getId())
                	   ->addOptionVote($optionId, $productId);
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(__('Review was saved succesfully'));
                $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
                return;
            } catch (Exception $e){
                die($e->getMessage());
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->getResponse()->setRedirect(Mage::getUrl('*/*/'));
        return;
    }
}