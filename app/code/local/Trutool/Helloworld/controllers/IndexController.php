<?php
class Trutool_Helloworld_Indexcontroller extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		//echo "hello world";
		$this->loadLayout();
		$this->renderLayout();
	}
	
	public function paramsAction(){
		echo'<dl>';
		foreach($this->getRequest()->getParams() as $key=>$val){
			echo '<dt><strong>Param: </strong>'.$key.'</dt>';
        	echo '<dt><strong>Value: </strong>'.$val.'</dt>';
    	}
   	 	echo '</dl>';
	}
	
	public function tCol(){
	$cataCol=Mage::getModel('catalog/category')
	->load(107)
	->getProductcollection();

	foreach($cataCol as $cc){
		$pList[]=$cc->getId(); 
	}
	var_dump($pList);
	}
}