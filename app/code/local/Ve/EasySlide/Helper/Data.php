<?php
/*------------------------------------------------------------------------
# Ve EasySlide - Banner Slide
# ------------------------------------------------------------------------
# author    Vsmart Extensions
# copyright Copyright (C) 2010 Vsmart-extensions.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.vsmart-extensions.com
# Technical Support:  Forum - http://www.vsmart-extensions.com
-------------------------------------------------------------------------*/
class Ve_EasySlide_Helper_Data extends Mage_Core_Helper_Abstract {		
	function get($var, $attributes){
		if(isset($attributes[$var])){
			return $attributes[$var];
		}		
    	return Mage::getStoreConfig("ve_easyslide/ve_easyslide/$var");
	}	  
}
?>