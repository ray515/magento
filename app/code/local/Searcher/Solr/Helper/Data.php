<?php
class Searcher_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function sURL(){
	//		return 'http://65.60.97.68:8983/solr/KTS1/';
			return 'http://127.0.01:8983/solr/KTS1/';
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
		return $collection;
	}
	
	public function searchCata($sCol){
		foreach ($sCol as $prod){
			$_cid=$prod->getCategoryIds();
			foreach($_cid as $_catID){
				$_cat = Mage::getSingleton('catalog/category')->load($_catID);
				$_catInfo=$_catID;
				$cataInfo[$_catID]=$_cat->getName();
			}
		}
		$cataOut='<ol id="cataOL">'; 
		foreach($cataInfo as $ci=>$ciCt){
				$cataOut=$cataOut.'<li class="cataLI" data-cId="'.$ci.'">'.$ciCt.'</li>';
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
			$priceOut=$priceOut.'<li class="priceLI">'.$psa1.' </li>';//('.count($psa1a).')
		}   $priceOut=$priceOut.'</ol><div id="fClrPrice" class="fClr">Reset</div>';
		return $priceOut;
	}
	
	public function prodOut($pCol){
		$outDat="";
		$outDat='<div id="prodList"><ol>';
		foreach($pCol as $prod){
			$outDat=$outDat.'<li>';
				$pClass=str_replace('"','',$prod->getName());
				$name=$prod->getName();
				$desc=$prod->getDescription();
				$sDesc=$prod->getShortDescription();
				//Product detail listing
				$tst = Mage::getResourceModel('eav/entity_attribute_collection')
					->setEntityTypeFilter(10)
					->getData();
				$attStr="";
				foreach($tst as $tst1){
					if($prod->getResource()->getAttribute($tst1['attribute_code'])->getFrontend()->getValue($prod)!=null && $tst1['frontend_label']!=null && $tst1['used_in_product_listing']==1 && $tst1['is_visible_on_front']==1){
						$attStr =$attStr.'<li>'.$tst1['frontend_label'].':'.$prod->getResource()->getAttribute($tst1['attribute_code'])->getFrontend()->getValue($prod).'</li>';
					}}
			$included=$prod->getIncluded();
			$qParam=array('qRec'=>$_GET['qRec']);
			$setLocation = Mage::getUrl('*/*',array(_query=>$qParam));
			$urlKey = $prod->getUrl_key().'.html';
			$price='$'.number_format($prod->getPrice(),2);
			$lgPict=Mage::helper('catalog/image')->init($prod,'image');
			$_helperCart = Mage::helper('checkout/cart')->getAddUrl($prod);
			$_helperComp = Mage::helper('catalog/product_compare')->getAddUrl($prod);
			$_manu=$prod->getAttributeText('manufacturer');
			

			$_cid=$prod->getCategoryIds();
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

			
// get layer info and html
			
			$cataOut='';
			foreach($cataInfo as $ci=>$ciCt){
				$cataOut=$cataOut.$ci.',';
			}
			$cataOut=rtrim($cataOut,',');
			$po1=rtrim(ltrim($price,'$'),'.00');
			$sl1=' 	<div id="'.$prod->getId().'o" class="'.$pClass.'" data-filter="'.$_manu.','.$po1.','.$cataOut.'" data-c="1" data-m="1" data-p="1">
						<div id="searchListing">
							<div id="slTarget1" data-me="'.$prod->getId().'o" class="'.$pClass.'">
									<div id="slImg"><img src="'.$lgPict->resize(125).'"></div>
									<div id="slTable">
											<span class="slTitle">'.$name.'</span><br/>
											<span class="slDesc">'.$desc.'</span>
									</div>
							</div>
							<div id="slAction">
								<span class="price listingPrice">'.$price.'</span>
								<p id="cartBut"><button type="button" id="listLink" data-link="'.$_helperCart.'" title="'.$this->__('Add to Cart') .'" class="addToCart button btn-cart" ><span><span>'.$this->__('Add to Cart').'</span></span></button></p>
							</div>			
						</div>
					</div>
					
					';
			
			$dialog='	<div id="dialog" class="'.$prod->getId().'o">
							<table id="diaTable1">
								<tr id="diaTableTop"><td id="diaTable1Img"><img src="'.$lgPict->resize(225).'"></td><td id="diaTable1Data">
										<div class="dataTabs">
												<ul>
													<li><a href="#tab-1">Product Information</a></li>
													<li><a href="#tab-2">Product Details</a></li>
													<li><a href="#tab-3">In The Box</a></li>
												</ul>
													<div id="tab-1"><p>'.$desc.'</p></div>
													<div id="tab-2"><p>'.$attStr.'</p></div>
												 	<div id="tab-3"><p><ol>'.$included.'</ol></p></div>
										</div>
										</td><tr>
								<tr id="diaTableBot"><td id="diaTable1Comp"><a href="'.Mage::getUrl('/').$urlKey.'">View Product Page</a></td><td id="diaTable1Action">
										<div id=diaTableActionPrice><input type=hidden class=addToCompData value="'.$_helperComp.'"><button type="button" id="compIt" data-link="'.$_helperComp.'" title="'.$this->__('Compare') .'" class="addToComp button btn-cart"><span><span class="price listingPrice">'.$this->__('Compare').'</span></span></button></div>
										<div id=diaTableActionPrice><button type="button" id="listLink" data-link="'.$_helperCart.'" title="'.$this->__('Add to Cart') .'" class="addToCart button btn-cart" ><span><span class="price listingPrice">'.$price.' '.$this->__('Add to Cart').'</span></span></button></div></td></tr>
							</table>
						</div>			';
			$outDat=$outDat.$sl1.$dialog;
			$outDat=$outDat.'</li>';
			
			}
			$outDat=$outDat.'</ol></div>'; //close prodList
			$outDat=$outDat.'</div>'; //close fList
			$outDat=$outDat.'<hr/>';
			$outDat=$outDat.'<div id="holder"></div>';
			return $outDat;
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
			$manuOut=$manuOut.'<li class="manuLI">'.$mi.'</li>';
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
	
	public function searchSug($term){
		$sugStr=urlencode($term);
		$url=Mage::helper('solr')->sURL().'suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);
		
		$res=$result['spellcheck']['suggestions'][1]['suggestion'];
		$jCt=0;
		$d1="";
		foreach($res as $res1){
			$d1=$d1.'<li><a href="?qRec='.$res1.'">'.$res1.'</li>';
			//$data[]=array("label"=>$res1);
			$jCt++;
		}
		//echo json_encode($d1);
		echo $d1;
	}
	
}
