<?php




class Ecom_Catalog_TreeController extends Ecom_Core_Controller_Zend_Admin_Action 
{

    /**
     * Index action
     *
     * Display categories home page
     *
     */
    function indexAction() 
    {
        $this->getResponse()->appendBody($this->_view->render('catalog/tree.php'));
    }
    
    function categoriesAction() 
    {
        $tree = Ecom::getModel('catalog','Categories');

        $data = $tree->getLevel($this->getRequest()->getPost('node'));
        $json = array();
        foreach ($data as $node) {
            $tmp = array();
            $tmp['text'] = $node->getData('name').':'.$node->getId();
            $tmp['id'] = $node->getId();
            $tmp['cls'] = 'folder';
            if (!$node->isParent()) {
                $tmp['leaf'] = 'true';    
            }
            $json[] = $tmp;            
        }
        unset($tmp);
        $this->getResponse()->setBody(json_encode($json));
    }
    
    
    function recentProductsAction()
    {
        // TODO: create system storage
        if (!is_array($_SESSION['OPEN_PRODUCTS'])) {
        	$_SESSION['OPEN_PRODUCTS'] = array();
        }
        
        $json = array();
        
        foreach ($_SESSION['OPEN_PRODUCTS'] as $productId) {
        	$json[] = array(
        	           'text'=>'Product #'.$productId, 
        	           'id'=>'recent-product-'.$productId, 
        	           'cls'=>'product', 
        	           'leaf'=>'true'
        	          );
        }

        $this->getResponse()->setBody(json_encode($json));
    }
    
    function recentSearchesAction()
    {
        
    }
    
    function savedSearchesAction()
    {
        
    }
    
    function __call($method, $args) 
    {
        var_dump($method);
    }
}
