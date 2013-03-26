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

class Ve_EasySlide_Model_System_Config_Source_ListType
{
    public function toOptionArray()
    {
        return array(
        	array('value'=>'', 'label'=>Mage::helper('ve_easyslide')->__('-- Please select --')),
            array('value'=>'latest', 'label'=>Mage::helper('ve_easyslide')->__('Latest')),
            array('value'=>'best_buy', 'label'=>Mage::helper('ve_easyslide')->__('Best Buy')),
            array('value'=>'most_viewed', 'label'=>Mage::helper('ve_easyslide')->__('Most Viewed')),
            array('value'=>'most_reviewed', 'label'=>Mage::helper('ve_easyslide')->__('Most Reviewed')),
            array('value'=>'top_rated', 'label'=>Mage::helper('ve_easyslide')->__('Top Rated')),
            array('value'=>'attribute', 'label'=>Mage::helper('ve_easyslide')->__('Featured Product')),
        );
    }    
}
