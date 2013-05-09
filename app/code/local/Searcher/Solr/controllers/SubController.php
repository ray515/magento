<?php
class Searcher_Solr_SubController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		if($_POST['action']==sc){$this->searchCol($_POST['type'],$_POST['term']);}
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
		if($filterBack){
			echo($filterBack);
		}else{
			return "Filter Error";
		}
	}
}