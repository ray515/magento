<?php
/**
 * Magento
 *
 * @category    Searcher_Solr
 * @package     Searcher_Solr
 * @subpackage  Solr
 * @abstract    Use - Reindex the solr search database
 * 				Methods
 * 				newIndex()
 */


/**
 * Product list
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Searcher_Solr_IndexController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		
	}
// search index
//TODO: only include active items
public function getCataIds($sDir){

	$prodCol=Mage::getModel('catalog/product')->getCollection();
	$prodCol->addAttributeToSelect('*');

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
	}

	$url					= Mage::helper('solr')->sURL().'update/json?commit=true';
	$Client 				= new Zend_Http_Client($url);
	$Client					->resetParameters()
	->setMethod(Zend_Http_Client::POST)
	->setHeaders('Content-type','application/json')
	->setRawData($jOut);
	$response				= $Client->request();
	echo $response.'<hr/>';

}

public function superNova($sDir){
	$xml					= "<delete><query>*:*</query></delete>";
	$Client					= new Zend_Http_Client(Mage::helper('solr')->sURL().'update');
	$Client					->resetParameters()
	->setMethod(Zend_Http_Client::POST)
	->setHeaders('Content-type','text/xml')
	->setRawData($xml);
	$response				= $Client->request();
	echo $response.'<hr/>';
}
	}