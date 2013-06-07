<?php
class TRU_ProdUp1_Block_Solrbuild extends Mage_Adminhtml_Block_Template
{

	
	public function __construct()
    {
        echo('test');
        parent::__construct();
        $this->setTemplate('page.phtml');
        $action = Mage::app()->getFrontController()->getAction();
        if ($action) {
            $this->addBodyClass($action->getFullActionName('-'));
        }
    }
}