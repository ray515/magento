<?php
class Searcher_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function sURL(){
			return 'http://65.60.97.68:8983/solr/KTS1/';
	}
	public function bURL(){
			return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
	}
	
	public function getResultUrl($query = null)
	{
		return $this->_getUrl('solr/result');
	}
	
	public function searchFilterBase($sCol){
		return count($sCol);
	}

	public function searchCol($term){
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
		return $collection;
	}
	
	public function searchCata($sCol){
		foreach ($sCol as $prod){
			//Cata Information//
			$_cid=$prod->getCategoryIds();
		//	echo('<hr/>');
		//	var_dump($_cid);
		//	echo('<hr/>');
			foreach($_cid as $_catID){
				$_cat=Mage::getModel('catalog/category')->load($_catID);
				$_catName=$_cat->getName();
				if(array_key_exists($_catName,$catArr)){
					$catArr[$_catName]++;
				}else{
					$catArr[$_catName]=1;
				}
				$cataInfo=$catArr;
			}
		}
		$cataOut='<ol id="cataOL">'; 
		foreach($cataInfo as $ci=>$ciCt){
			//if($ci != 'Root Catalog'){
				$cataOut=$cataOut.'<li class="cataLI">'.$ci.' ('.$ciCt.')</li>';
			//}
		}
		$cataOut=$cataOut.'</ol><div id="fClrCata" class="fClr">Reset</div>';
		return $cataOut;
	}
	
	public function searchPrice($sCol){
		foreach ($sCol as $prod){
			//Price Information//
			$_price=$prod->getPrice();
			if(array_key_exists($_price,$priceArr)){
				$priceArr[$_price]++;
			}else{
				$priceArr[$_price]=1;
			}
			$priceInfo=$priceArr;
		}
		// price structure
		ksort($priceInfo);
		$priceTempArr=array_sum(array_keys($priceInfo));
		$priceAvg=$priceTempArr/count($priceInfo);
		$pv=array_keys($priceInfo);
		foreach($pv as $nv){
			$n=intval($nv);
			if($n>999){
				$priceSetArr["$1000.00 or more"][]=$n;
			}elseif($n>=500){
				$priceSetArr["$500.00 to $999.99"][]=$n;
			}elseif($n>=200){
				$priceSetArr["$200.00 to $499.99"][]=$n;
			}elseif($n>=100){
				$priceSetArr["$100.00 to $199.99"][]=$n;
			}elseif($n>=50){
				$priceSetArr["$50.00 to $99.99"][]=$n;
			}elseif($n>=10){
				$priceSetArr["$10.00 to $49.99"][]=$n;
			}elseif($n>=1){
				$priceSetArr["$1.00 to $9.99"][]=$n;
			}else{
				$priceSetArr["$0 to $.99"][]=$n;
			}
		}
		$priceOut='<ol id="priceOL">';
		foreach($priceSetArr as $psa1=>$psa1a){
			$psa1Fix=str_getcsv($psa1,'(');
			$priceOut=$priceOut.'<li class="priceLI">'.$psa1.' ('.count($psa1a).')</li>';
		}   $priceOut=$priceOut.'</ol><div id="fClrPrice" class="fClr">Reset</div>';
		return $priceOut;
	}
	
	public function searchManu($sCol){
		foreach ($sCol as $prod){
			//Manufacturer Information//
			$_manu=$prod->getAttributeText('manufacturer');
			$_ctManu=$prod->getCt_brand();
			If($_manu){
				if(array_key_exists($_catName,$catArr)){
					$manuArr[$_manu]++;
				}else{
					$manuArr[$_manu]=1;
				}
			}elseif($_ctManu){
				if(array_key_exists($_catName,$catArr)){
					$manuArr[$_ctManu]++;
				}else{
					$manuArr[$_ctManu]=1;
				}
			}else{echo 'ERROR';}
			$manuInfo=$manuArr;
		}
		$manuOut='<ol id="manuOL">';
		foreach($manuInfo as $mi=>$miCt){
			$manuOut=$manuOut.'<li class="manuLI">'.$mi.' ('.$miCt.')</li>';
		}
		$manuOut=$manuOut.'</ol><div id="fClrManu" class="fClr">Reset</div>';
		return $manuOut;
	}
	
	public function searchMage($sRec){
		$sugStr=urlencode($sRec);
		//echo('search str: '.$sRec);
		$url=Mage::helper('solr')->sURL().'suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);
		//var_dump($result);
		
		$res=$result['spellcheck']['suggestions'][1]['suggestion'];
		
		$out='<ol>';
		foreach($res as $res1){
			$out=$out.'<li>'.$res1.'</li>';
		}
		$out=$out.'</ol>';
		//var_dump($out);
		return $out;
	}
}