<?php
class Searcher_Sajax_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		echo "test";
			if($_POST['qRec'] && $_POST['usr']){
				$this->searchMage($_POST['usr'], $_POST['qRec']);
				//print_r('<div id="rWrap">you are searching for: '.$_POST['usr'].'<br/>');
			}elseif($_POST['qRec']){
				print_r('<div id="rWrap">you are searching for: '.$_POST['qRec'].'<br/>');
			}else{
				print_r('<div id="rWrap">this is a test</div>');
			}
			if($_REQUEST['qRec1']){
				$this->searchMage1($_REQUEST['usr'], $_REQUEST['qRec1']);
				echo "you are here.";
			}
		}
	

	public function solUrl(){ return 'http://65.60.97.68:8983/solr/'; }
	
	public function getCataIds($sDir){
		print_r('<h3>OutPut CSV</h3>');
		
		//solr connection
		//echo phpInfo();
		
		
		$prodCol=Mage::getModel('catalog/product')->getCollection();
		$prodCol->addAttributeToSelect('*');
		echo "<hr/>";
		foreach($prodCol as $p1){			
			$cata1=$p1->getCategoryIds();
			$tOut['id']=$p1->getId();
					
			$cOut="";
			foreach($cata1 as $cata){
				$_cat=Mage::getModel('catalog/category')->load($cata);
				$cOut=$cOut.$_cat->getName()." ";
			}
			rtrim($cOut,',');
			$tOut['cat']=str_replace('+','',$cOut);
			$tOut['sku']=$p1->getSku();
			$nameTemp = strip_tags($p1->getName());
			$nameTemp = str_replace('&mdash; ','',$nameTemp);
			$tOut['name']=strip_tags($nameTemp);
			$tOut['manu']=$p1->getAttributeText('manufacturer');
			$tOut['url']=$p1->getProductUrl();
			$tOut['features']=strip_tags($p1->getShortDescription());
			$tOut1['doc']=$tOut;
			$tOut2['add']=$tOut1;
			$jOut=$jOut.json_encode($tOut2);
		}
		//echo $jOut;
		echo '<br/><br/>';
		echo "<hr/>";
		
		$url='http://65.60.97.68:8983/solr/'.$sDir.'/update/json?commit=true';
		$Client = new Zend_Http_Client($url);
		$Client->resetParameters();
		$Client->setMethod(Zend_Http_Client::POST);
		$Client->setHeaders('Content-type','application/json');
		$Client->setRawData($jOut);
		$response=$Client->request();
		echo $response.'<hr/>';
	
	}

	public function searchMage($sDir,$sugStr){
		$sugStr=urlencode($sugStr);
		$url=$this->solUrl().$sDir.'/suggest?wt=php&q='.$sugStr;
		eval("\$result=".file_get_contents($url).";");
		//$result=eval(file_get_contents($url));
		$res=$result['spellcheck']['suggestions'][$sugStr]['suggestion'];
		$rCt=count($res);
		print_r('<div id="rWrap">');
	 	print_r('<h3>Suggestions</h3>');
		foreach($res as $res1){
			print_r($res1.'<br/>');
		}
		print_r('<div>');
		//var_dump($result['spellcheck']['suggestions']);
	}
	
	public function searchMage1($usr,$sugStr){
		$sugStr='cobra';
		$sugStr=urlencode($sugStr);
		$url='http://65.50.97.68:8983/solr/'.$usr.'/suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);

		$res=$result['spellcheck']['suggestions'][1]['suggestion'];
		$outty="";
		foreach($res as $res1){
			$outty = $outty.$res1.',';
			//$data[]=array(
			//		'label'=>$res1,
			//		'value'=>$res1
			//		);
		}
		$outty=rtrim($outty,',');
		var_dump($result);
		echo $outty;
		//flush();
	}
	
	public function searchRes($term, $sDir){
		$req=urlencode($term);
		$url='http://65.60.97.68:8983/solr/'.$sDir.'/select?q='.$req.'&wt=php&indent=true';
		//$datBack=file_get_contents($url);
		eval("\$result=".file_get_contents($url).";");
		$rOut=$result['response']['docs'];
		foreach($rOut as $rOut1){
			$sID[]=$rOut1['sku'];
		}
		
		$collection = Mage::getModel('catalog/product')->getCollection(); // Start a new collection containing products
		$collection->addAttributeToSelect('*');     // Tell magento to load all the product attribute data, change this if you need just a subset of data in your template file
		$i = 0; $filters = array(); // Init some vars to add our filters
		foreach($sID as $sku)
			$filters[$i++] = array('attribute'=>'sku','eq'=>$sku); // For each SKU add a filter that defines that the product will be selected is it's sku is in our array
		$collection->addFieldToFilter($filters); // Add the filter
		//$this->setCollection($collection); // Set this collection to be the one we're using
		echo count($collection).'<br/>';
		return $collection; // Return it
		//return $sID;
	}
}