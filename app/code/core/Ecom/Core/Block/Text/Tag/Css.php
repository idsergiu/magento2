<?php



/**
 * Base html block
 *
 * @copyright  Varien, 2007
 * @version    1.0 
 * @author	   Moshe Gurvich <moshe@varien.com>
 * @date       Thu Feb 08 05:56:43 EET 2007
 */

class Ecom_Core_Block_Text_Tag_Css extends Ecom_Core_Block_Text_Tag
{
	function __construct()
	{
		parent::__construct();

		$this->setAttribute(array(
		  'tagName'=>'link',
		  'tagParams'=>array('rel'=>'stylesheet', 'type'=>'text/css', 'media'=>'all'),
		));
	}
	
	function setHref($href)
	{
	    return $this->setTagParam('href', Ecom::getBaseUrl('skin').$href);
	}
}// Class Ecom_Core_Block_List END