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
class Ve_EasySlide_Model_System_Config_Source_ListDirection{
    public function toOptionArray()
    {
        return array(        	
            array('value'=>'left', 'label'=>Mage::helper('ve_easyslide')->__('Left')),
            array('value'=>'right', 'label'=>Mage::helper('ve_easyslide')->__('Right')),
        );
    }    
}