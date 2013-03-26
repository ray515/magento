<?php
/**
 * Magento
 * 
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Searcher Solr IndexController
 *
 * @category   Searcher
 * @package    Searcher_Sajax
 * @author     KTS Web Team <eric.gould@etoolsrus.com>
 */

/**
 * 
 * @author EricG
 *
 */
class Searcher_Sajax_AjaxController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		$this->loadLayout(sajax.phtml);
		$this->renderLayout();
		//echo "controller test.";
	}
}