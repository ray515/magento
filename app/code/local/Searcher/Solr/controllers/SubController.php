<?php
class Searcher_Solr_SubController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		if($_REQUEST['action']==sc){$this->searchCol($_REQUEST['type'],$_REQUEST['term']);}
		if($_REQUEST['action']=='priceClick'){$this->priceClick($_REQUEST['lp'],$_REQUEST['hp'],$_REQUEST['sfp']);}
		if($_REQUEST['action']=='catClick'){$this->catClick($_REQUEST['cat'],$_REQUEST['sfc']);}
		if($_REQUEST['action']=='priceClr'){$this->priceClr();}
		if($_REQUEST['action']=='manClr'){$this->manClr();}
		if($_REQUEST['action']=='catClr'){$this->catClr();}
	}
	
	public function priceClick($lp,$hp,$sf){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('sID1');
		//more in hp fix
		if($hp=='ore'){$hp=1000000;}
		
		$sCol = Mage::getResourceModel('catalog/product_collection')
		->addIdFilter(array($searchSet))
		->addAttributeToFilter('price',array('lt'=>$hp))
		->addAttributetoFilter('price',array('gt'=>$lp))
		->load();
		foreach($sCol as $sc){
			$pcTrans[]=$sc->getId();
		}
		//var_dump($pcTrans);
		$session->setData('priceClick',$pcTrans);
		$session->setData('solrFilter-price',$sf);
		echo($this->oneFilterArr());
	}
	
	public function catClick($cat,$sf){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('sID1');

		$cModel = Mage::getModel('catalog/category')->load($cat);
		$cCol	= Mage::getResourceModel('catalog/product_collection')
				->addIdFilter(array($searchSet))
				->addCategoryFilter($cModel)
				->addAttributeToSelect('id')
				->load();
//		var_dump($cCol);
		
		foreach($cCol as $cc){
			$pcTrans[]=$cc->getId();
		}
		$session->setData('catClick',$pcTrans);
		$session->setData('solrFilter-cat',$sf);
		//echo('You have chosen: '.json_encode($pcTrans));
		echo($this->oneFilterArr());
	}
	
	public function manuClick($manu){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('sID1');
		$mCol = Mage::getResourceModel('catalog/product_collection')
		->addIdFilter(array($searchSet))
		->addAttributeToFilter('manufacturer',$manu)
//		->addAttributetoFilter('price',array('gt'=>$lp))
		->load();
		foreach($mCol as $sc){
			$pcTrans[]=$sc->getId();
		}
		echo('Manufacturer Results: '.json_encode($pcTrans));
	}
	
	public function priceClr(){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$session->setData('priceClick',null);
		$session->setData('solrFilter-price',null);
		$this->oneFilterArr();
		return;
	}
	
	public function manuClr(){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$session->setData('manClick',null);
		$session->setData('solrFilter-manu',null);
		$this->oneFilterArr();
		return;
	}
	
	public function catClr(){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$session->setData('catClick',null);
		$session->setData('solrFilter-cat',null);
		$this->oneFilterArr();
		return;
	}
	
	/*
	 * Combines all filter output arrays into a single merged,unique,sorted array of product ID's to be used by the list filter.
	 * uses the Session variables for price,cata,manu
	 */
	public function oneFilterArr(){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$masterArr = $session->getData('sID1');
		if($session->getData('priceClick')!=null){
			$priceArr = $session->getData('priceClick');
		}else{
			$priceArr = array();
		}
		if($session->getData('catClick')!=null){
			$catArr = $session->getData('catClick');
		}else{
			$catArr = array();
		}
		if($session->getData('manClick')!=null){
			$manArr = $session->getData('manClick');
		}else{
			$manArr = array();
		}
		$startCt = count($priceArr)+count($catArr)+count($manArr);
/*		$oneArr=array_merge($priceArr,$catArr,$manArr);
		$mergeCt = count($oneArr);
		$oneArr=array_unique($oneArr);
		
*/
		$dieArr1=array_diff($masterArr, $priceArr);
		$masterArr1 = array_diff($masterArr, $dieArr);
		$dieArr2=array_diff($masterArr1,$catArr);
		$masterArr2 = array_diff($masterArr1,$dieArr2);
		$oneArr=$masterArr2;
		$endCt=count($oneArr);
		foreach($oneArr as $oa=>$oaVal){
			$oneArr1[]=$oaVal;
		}
		if($startCt>0){
			$session->setData('oneArr',$oneArr1);
		}else{
			$session->setData('oneArr',null);
		}
		$output = "<p>One Array Filter Output</p><ul><li>Starting count:".$startCt."</li><li>Merge Count:".$mergeCt."</li><li>End Count: ".$endCt."</li><li>Result Array: <br/>".json_encode($oneArr1)."</li></ul>";
		return $output;
	}
	
	public function searchCol($type,$term){
		// this should return the product collection from the search.
	
		$resStr=urlencode($term);
		$url=Mage::helper('solr')->sURL().'select?wt=json&q='.$resStr;
		$solrPg=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);
	
		foreach($result['response']['docs'] as $rOut1){
			$sID[] = $rOut1['sku'];
		}
	
		$collection = Mage::getModel('catalog/product')->getCollection();
		$i = 0; $filters = array();
		foreach($sID as $sku){ $filters[$i++] = array('attribute'=>'sku','eq'=>$sku); }
		$collection->addFieldToFilter($filters);
		$collection->addAttributeToSelect('*');
		//$this->collection=$collection;
		//return $collection;
		if($type=='price'){$filterBack=Mage::helper('solr')->searchPrice($collection);}
		if($type=='manu'){$filterBack=Mage::helper('solr')->searchManu($collection);}
		if($type=='cata'){$filterBack=Mage::helper('solr')->searchCata($collection);}
		if($type=='sug'){$filterBack=Mage::helper('solr')->searchSug($term);}
		if($type=='prod'){$filterBack=Mage::helper('solr')->prodOut($collection);}
		if($filterBack){
			echo($filterBack);
		}else{
			return "Filter Error";
		}
	}
}