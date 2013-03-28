<?php
class Searcher_Solr_Helper_Data extends Mage_Core_Helper_Abstract
{
	// constants
	const SURL='http://65.60.97.68:8983/solr/KTS';
	
	public function getResultUrl($query = null)
	{
		//return $this->_getUrl('catalogsearch/result', array(
		//		'_query' => array(self::QUERY_VAR_NAME => $query),
		//		'_secure' => Mage::app()->getFrontController()->getRequest()->isSecure()
		//));
		return $this->_getUrl('solr/result');
	}
	public function ajaxRes(){
		return 'this is a test';
	}
	
	public function searchFilterBase($sCol){
		return count($sCol);
	}

	public function searchCata($sCol){
		foreach ($sCol as $prod){
			//Cata Information//
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
		}
		$cataOut='<ol>'; 
		foreach($cataInfo as $ci=>$ciCt){
			if($ci != 'Root Catalog'){
				$cataOut=$cataOut.'<li>'.$ci.' ('.$ciCt.')</li>';
			}
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
		$priceOut='<ol>';
		foreach($priceSetArr as $psa1=>$psa1a){
			$psa1Fix=str_getcsv($psa1,'(');
			$priceOut=$priceOut.'<li class="liStart">'.$psa1.' ('.count($psa1a).')</li>';
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
		$manuOut='<ol>';
		foreach($manuInfo as $mi=>$miCt){
			$manuOut=$manuOut.'<li>'.$mi.' ('.$miCt.')</li>';
		}
		$manuOut=$manuOut.'</ol><div id="fClrManu" class="fClr">Reset</div>';
		return $manuOut;
	}
	
	public function searchMage($sRec){
		$sugStr=urlencode($sRec);
		$url=self::SURL.'/suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);
	
		$res=$result['spellcheck']['suggestions'][1]['suggestion'];
		$out='<ol>';
		foreach($res as $res1){
			$out=$out.'<li>'.$res1.'</li>';
		}
		$out=$out.'</ol>';
		return $out;
	}
	
	public function toolBar($tCol,$dispCount){
		$colCt=count($tCol);
		$perPage=$dispCount;
		$lastPg=number_format($colCt/$perPage,0);
		if($lastPg % 3){
			$lastpg++;
		}
		$firstPg=1;
		$setLimit=array(5,10,50);
		echo "first :".$firstPg."<br/> last: ".$lastPg."<br/> total: ".$colCt."<br/> per pg: ".$perPage."<br/>";
		//if($this->getCollection()->getSize()){
		if($colCt){
			echo '<div class="toolbar"><div class="pager"><p class="amount">';
			//if($this->getLastPageNum()>1){
			if($lastPg>1){
				//echo  $this->__('Items %s to %s of %s total', $this->getFirstNum(), $this->getLastNum(), $this->getTotalNum());
				echo  $this->__('Items %s to %s of %s total', $firstPg, $lastPg, $colCt);
		    }else{
		    	//echo  '<strong>'.$this->__("%s Item(s)", $this->getTotalNum()).'</strong>';
		    	echo  '<strong>'.$this->__("%s Item(s)", $colCt).'</strong>';
		    }
	   		echo  '</p>';
		
	   		echo  '<div class="limiter"><label>'.$this->__("Show").'</label><select onchange="setLocation(this.value)">';
		    //foreach ($this->getAvailableLimit() as  $_key=>$_limit){
		    foreach(setLimit as $_key=>$_limit){
		    	echo '<option value="'.$this->getLimitUrl($_key).'"'; if($this->isLimitCurrent($_key)){echo 'selected="selected"';} echo '>';
		        echo $_limit.'</option>';
		 	}
		        echo '</select>'.$this->__('per page').'</div>';
		        echo $this->getPagerHtml().'</div>';
		
		    if( $this->isExpanded() ){
		    	echo '<div class="sorter">';
		        if( $this->isEnabledViewSwitcher() ){
		        	echo '<p class="view-mode">';
		            	$_modes = $this->getModes();
		            if($_modes && count($_modes)>1){
		            	echo '<label>'.$this->__('View as').':</label>';
		            foreach ($this->getModes() as $_code=>$_label){
		                if($this->isModeActive($_code)){
		                    echo '<strong title="'.$_label.'" class="'.strtolower($_code).'">'.$_label.'</strong>&nbsp;';
		                }else{
		                    echo '<a href="'.$this->getModeUrl($_code).'" title="'.$_label.'" class="'.strtolower($_code).'">'.$_label.'</a>&nbsp;';
		                } //end if
		            } //end foreach
		            } //end if
		        echo '</p>';
		        } //end if
		    
		        echo '<div class="sort-by"><label>'.$this->__("Sort By").'</label><select onchange="setLocation(this.value)">';
		            foreach($this->getAvailableOrders() as $_key=>$_order){
		                echo '<option value="'.$this->getOrderUrl($_key, 'asc').'"'; if($this->isOrderCurrent($_key)){echo 'selected="selected"';} echo '>';
		                    echo $this->__($_order);
		                echo '</option>';
		            } // end foreach
		            echo '</select>';
		            if($this->getCurrentDirection() == 'desc'){
		                echo '<a href="'.$this->getOrderUrl(null, 'asc').'" title="'.$this->__('Set Ascending Direction').'"><img src="'.$this->getSkinUrl('images/i_desc_arrow.gif').'" alt="'.$this->__('Set Ascending Direction').'" class="v-middle" /></a>';
		            }else{
		                echo '<a href="'.$this->getOrderUrl(null, 'desc').'" title="'.$this->__('Set Descending Direction').'"><img src="'.$this->getSkinUrl('images/i_asc_arrow.gif').'" alt="'.$this->__('Set Descending Direction').'" class="v-middle" /></a>';
		            } //end if
		        echo '</div>';
		    echo '</div>';
		    } //end if
		echo '</div>';
		} // end if
	}
	
}