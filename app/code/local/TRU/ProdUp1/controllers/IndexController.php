<?php
class TRU_ProdUp1_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/produp1?id=15 
    	 *  or
    	 * http://site.com/produp1/id/15 	
    	 */			
		$this->loadLayout();     
		$this->renderLayout();
    }
}