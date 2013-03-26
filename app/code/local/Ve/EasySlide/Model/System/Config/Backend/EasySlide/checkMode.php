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
class Ve_EasySlide_Model_System_Config_Backend_EasySlide_checkMode extends Mage_Core_Model_Config_Data
{

    protected function _beforeSave(){
    	$groups = $this->getData('groups');
    	$datas = $groups['ve_easyslide'];
    	if($datas['fields']['mode']['value']=='category' && $datas['fields']['catsid']['value']==''){
    		throw new Exception(Mage::helper('ve_easyslide')->__('Please enter list of Categories ID.'));
    	}
       	elseif($datas['fields']['mode']['value']=='category' && $datas['fields']['leading_product']['value']=='' && $datas['fields']['intro_product']['value']=='' ){
    		throw new Exception(Mage::helper('ve_easyslide')->__('Please enter Leading or Intro number.'));
    	}
        return $this;
    }

}
