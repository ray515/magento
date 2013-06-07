<?php
/**
 * Magento
 *
 * @category    code_local_TRU
 * @package     TRU_ProdUp1_SolrBuild
 * @subpackage  Solr
 * @abstract    Use - Reindex and Purge the solr search database.
 * 				Methods
 * 					tester()
 * 						just a page ping.
 * 					bigBang() 
 * 						Init capture of all products to be found in search.
 * 						Returns - Table with info and Solr Response.
 * 				 	superNova()
 * 						Kills all records in Solr
 * 						Returns - Warning message and Solr Response.
 */

class Tru_ProdUp1_Solrbuild_CrawlController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		if($_REQUEST['action']=='test'){
			$this->tester();
		}
		if($_REQUEST['action']=='bigBang'){$this->bigBang();}
		if($_REQUEST['action']=='superNova'){$this->superNova();}
	}
	
public function tester(){
	echo('you found me.');
}

public function bigBang(){
	$prodCol1=Mage::getModel('catalog/product')
	->getCollection()
	->addAttributeToSelect('id')
	->addIdFilter('6359')
	->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
	
	$setId=0;
	$prodCt=0;
	foreach($prodCol1 as $pcCrawl){
		
		if($prodCt==500){
			$setId++;
			$prodCt=0;
		}
		$crawlSet[$setId][]=$pcCrawl->getId();	
		$prodCt++;
	}
	$pcCt=count($prodCol1);
	$grpCt=0;
	echo('<h1>Site Index Complete</h1><p>'.$pcCt.' Records have been added to the KTS Solr Search Engin</p><table width="100%"><tr><th>Record Count</th><th>Record Group</th><th>Record ID</th><th>Record Name</th></tr>');	
	foreach($crawlSet as $cs1){
		$prodCol="";
		$prodCol=Mage::getModel('catalog/product')
			->getCollection()
			->addAttributeToSelect('*')
			->addFieldToFilter('status',Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			->addIdFilter(array($cs1));		
		$pcCt1=count($prodCol);
		$fct=0;
		
		foreach($prodCol as $p1){
			$cata1				= $p1->getCategoryIds();
			$tOut['id']			= $p1->getId();
			$cOut="";
			foreach($cata1 as $cata){
				$_cat=Mage::getModel('catalog/category')->load($cata);
				$cOut=$cOut.$_cat->getName()." ";
			}
			$priceIn			= str_getcsv($p1->getPrice(),'.');
			$priceOut			= $priceIn[0].'.00';
			$cOut				= rtrim($cOut,',');
			$tOut['cat']		= rtrim(str_replace('+','',$cOut));
			$tOut['sku']		= $p1->getSku();
			$nameTemp 			= strip_tags($p1->getName());
			$nameTemp 			= str_replace('&mdash; ','',$nameTemp);
			$tOut['name']		= strip_tags($nameTemp);
			$tOut['manu']		= $p1->getAttributeText('manufacturer');
			$tOut['url']		= $p1->getProductUrl();
			$tOut['features']	= strip_tags($p1->getShortDescription());
			$tOut['price']		= (float)$priceOut;
			$tOut1['doc']		= $tOut;
			$tOut2['add']		= $tOut1;
			$jOut				= $jOut.json_encode($tOut2);
			$fct++;
			echo('<tr><td>'.$fct.'</td><td>'.$grpCt.'</td><td>'.$tOut['id'].'</td><td>'.$tOut['name'].'</td></tr>');
		}
		$grpCt++;
	
		echo('</table>');
		
		$url					= Mage::helper('solr')->sURL().'update/json?commit=true';
		$Client 				= new Zend_Http_Client($url);
		$Client					->resetParameters()
								->setMethod(Zend_Http_Client::POST)
								->setHeaders('Content-type','application/json')
								->setRawData($jOut);
		$response				= $Client->request();
		echo ('<hr/>'.$response.'<hr/>');
	}		
}

public function superNova(){
	$xml					= "<delete><query>*:*</query></delete>";
	$Client					= new Zend_Http_Client('http://65.60.97.68:8983/solr/KTS1/update');
	$Client					->resetParameters()
	->setMethod(Zend_Http_Client::POST)
	->setHeaders('Content-type','text/xml')
	->setRawData($xml);
	$response				= $Client->request();
	
	echo('<h1>Site Index Clear</h1><p>The KTS Solr Search Engin has been purged of all records</p><p>**WARNING**<br/>THIS WEBSITE CANNOT PROPERLY WITHOUT RECORDS IN THE KTS SOLR SEARCH ENGIN. If you are having trouble creating a new site index please contact Eric (eric.gould@etoolsrus.com) ASAP.</p>');
	echo ('<hr/>'.$response.'<hr/>');
}
	}