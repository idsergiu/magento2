<?php



/**
 * Category View block
 *
 * @package    Ecom
 * @module     Catalog
 * @copyright  Varien (c) 2007 (http://www.varien.com)
 */
class Ecom_Catalog_Block_Category_View extends Ecom_Core_Block_Template
{

    private $breadcrumbs;
    /**
     * Product Collection
     *
     * @var Ecom_Catalog_Model_Mysql4_Product_Collection
     */
    private $prodCollection;
    private $currentCategory;

    public function __construct()
    {
        parent::__construct();
        $this->setViewName('Ecom_Catalog', 'category/view');
    }

    public function loadData(Zend_Controller_Request_Http $request)
    {
        $this->currentCategory = $this->getAttribute('category');

        $breadcrumbs = Ecom::createBlock('catalog_breadcrumbs', 'catalog.breadcrumbs');
        $breadcrumbs->addCrumb('home', array('label'=>'Home','title'=>'Go to home page','link'=>Ecom::getBaseUrl().'/'));
        $breadcrumbs->addCrumb('category', array('label'=>$this->currentCategory->getData('name')));
        $this->setChild('breadcrumbs', $breadcrumbs);

        $this->prodCollection = Ecom::getModel('catalog','product_collection');

        $this->prodCollection->addFilter('website_id', Ecom::getCurentWebsite(), 'and');
        $this->prodCollection->addFilter('category_id', $this->currentCategory->getId() , 'and');

        Ecom::getBlock('catalog.leftnav.bytopic')->assign('currentCategoryId',$this->currentCategory->getId());
        Ecom::getBlock('catalog.leftnav.byproduct')->assign('currentCategoryId',$this->currentCategory->getId());

        $page = $request->getParam('p',1);
        $this->prodCollection->setOrder($request->getParam('order','name'), $request->getParam('dir','asc'));
        $this->prodCollection->setCurPage($page);
        $this->prodCollection->loadData();

        $this->assign('category', $this->currentCategory);
        $this->assign('productCollection', $this->prodCollection);
        
        $pageUrl = clone $request;
        $this->assign('pageUrl', $pageUrl);
        
        $sortUrl = clone $request;
        $sortUrl->setParam('p', 1)->setParam('dir', 'asc');
        $this->assign('sortUrl', $sortUrl);
        
        $this->assign('sortValue', $request->getParam('order','name').'_'.$request->getParam('dir','asc'));
    }
}