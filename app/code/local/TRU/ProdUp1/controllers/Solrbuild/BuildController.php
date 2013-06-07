<?php
class Tru_ProdUp1_Solrbuild_BuildController extends Mage_Adminhtml_Controller_Action{
	public function indexAction(){
$this->loadLayout();
$this->getLayout()->getBlock('root')->setTemplate('solrbuild/build.phtml');
$this->renderLayout();
	}
}