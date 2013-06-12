<?php
class Searcher_Solr_SubController extends Mage_Core_Controller_Front_Action{
	public function indexAction(){
		if($_REQUEST['action']==sc){$this->searchCol($_REQUEST['type'],$_REQUEST['term']);}
		if($_REQUEST['action']=='priceClick'){$this->priceClick($_REQUEST['lp'],$_REQUEST['hp'],$_REQUEST['sfp']);}
		if($_REQUEST['action']=='catClick'){$this->catClick($_REQUEST['cat'],$_REQUEST['sfc']);}
		if($_REQUEST['action']=='manClick'){$this->manClick($_REQUEST['man'],$_REQUEST['sfm']);}
		if($_REQUEST['action']=='priceClr'){$this->priceClr();}
		if($_REQUEST['action']=='manClr'){$this->manClr();}
		if($_REQUEST['action']=='catClr'){$this->catClr();}
	}
	
	public function priceClick($lp,$hp,$sf){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('aFilt');
		//more in hp fix
		if($hp=='ore'){$hp=1000000;}
		
		$sCol = Mage::getResourceModel('catalog/product_collection')
		->addIdFilter(array($searchSet))
		->addAttributeToFilter('price',array('lt'=>$hp))
		->addAttributetoFilter('price',array('gt'=>$lp));
		foreach($sCol as $sc){
			$pcTrans[]=$sc->getId();
		}
		$session->setData('priceClick',$pcTrans);
		$session->setData('solrFilter-price',$sf);
		$this->oneFilterArr();
	//	echo('<script>alert("'. count($session->getData('aFilt')) .'");</script>');
	//	echo('<script>alert("'. count($sCol) .'");</script>');
		return;
	}
	
	public function catClick($cat,$sf){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('aFilt');

		$cModel = Mage::getModel('catalog/category')->load($cat);
		$cCol	= Mage::getResourceModel('catalog/product_collection')
				->addIdFilter(array($searchSet))
				->addCategoryFilter($cModel)
				->addAttributeToSelect('id');
		
		foreach($cCol as $cc){ $pcTrans[]=$cc->getId(); }
		$session->setData('catClick',$pcTrans);
		$session->setData('solrFilter-cat',$sf);
		$this->oneFilterArr();
		return;
	}
	
	public function manClick($man,$sf){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$searchSet = $session->getData('aFilt');
		$mCol = Mage::getResourceModel('catalog/product_collection')
		->addIdFilter(array($searchSet))
		->addAttributeToFilter('manufacturer', $man);
		
		foreach($mCol as $sm){	$pcTrans[]=$sm->getId(); }
		$session->setData('manClick',$pcTrans);
		$session->setData('solrFilter-manu',$sf);
		$this->oneFilterArr();
		return;
	}
	
	public function priceClr(){
		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$session->setData('priceClick',null);
		$session->setData('solrFilter-price',null);
		$this->oneFilterArr();
		return;
	}
	
	public function manClr(){
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
		
		$masterArr = $session->getData('aFilt');
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

		$finalArr="";
		$t1Arr="";
		$t2Arr="";
		$t3Arr="";
		if(count($priceArr)>0){
			foreach($priceArr as $pa){
				foreach ($masterArr as $ma){
					if($ma==$pa){
						$t1Arr[]=$pa;
					}
				}
			}
		}else{
			$t1Arr=$masterArr;
		}
//		echo('Add Price: <br/> Count: '.count($t1Arr).'<br/>status: '.$session->getData('solrFilter-price').'<br/>Values: <br/>'.json_encode($t1Arr).'<hr/>');
		
		if(count($catArr)>0){
			foreach($catArr as  $ca){
				foreach($t1Arr as $t1){
					if($ca==$t1){
						$t2Arr[]=$ca;
					}
				}
			}
		}else{
			$mct = count($masterArr);
			$ct1 = count($t1Arr);
			if($ct1<$mct){
				$t2Arr=$t1Arr;$t1Arr="";
			}else{
				$t2Arr=$masterArr;
			}
		}
//		echo('Add Cat: <br/> Count: '.count($t2Arr).'<br/>'.json_encode($t2Arr).'<hr/>');
		
		if(count($manArr)>0){
			foreach($manArr as  $ma){
				foreach($t2Arr as $t2){
					if($ma==$t2){
						$t3Arr[]=$ma;
					}
				}
			}
		}else{
			$mct=count($masterArr);
			$ct2=count($t2Arr);
			if($ct2<$mct){
				$t3Arr=$t2Arr;$t2Arr="";
			}else{
				$t3Arr=$masterArr;$t2Arr="";
			}
		}
//		echo('Add Manu: <br/> Count: '.count($t3Arr).'<br/>'.json_encode($t3Arr).'<hr/>');
		$session->setData('oneArr',$t3Arr);
		return;
	}
/* moved to modern/template/catalog/layer/view.phtml	
	public function searchCol($type,$term){
		// this should return the product collection from the search.

		$session = Mage::getSingleton('core/session', array('name'=>'frontend'));
		$collection = Mage::getResourceModel('catalog/product_collection')
		->addAttributeToSelect('price','manufacturer','category_ids')
		->addIdFilter($session->getData('aFilt'));
		
		foreach ($collection as $prodp){
//Price Information//
			$_price=$prodp->getPrice();
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
		
		$pFilt=trim($session->getData('solrFilter-price'));
		$priceOut='<dt>Price</dt><dd><ol id="priceOL">';
		foreach($priceSetArr as $psa1=>$psa1a){
			$psa1Fix=str_getcsv($psa1,'(');
			if($psa1 == $pFilt){
				$priceOut.= '<li class="priceLI liTouch">'.$psa1.' </li>';
			}else{
				$priceOut.='<li class="priceLI liStart">'.$psa1.' </li>';//('.count($psa1a).')
			}
		}   $priceOut.= '</ol><div id="fClrPrice" class="fClr">Reset</div></dd>';

// cat information		
		foreach ($collection as $prodc){
			$_cid=$prodc->getCategoryIds();
			foreach($_cid as $_catID){
				$_cat = Mage::getSingleton('catalog/category')->load($_catID);
				$_catInfo=$_catID;
				$cataInfo[$_catID]=$_cat->getName();
			}
		}
		$pFilt=trim($session->getData('solrFilter-cat'));
		$cataOut='<dt>Category</dt><dd><ol id="cataOL">';
		foreach($cataInfo as $ci=>$ciCt){
			if($pFilt == $ciCt){
				$cataOut=$cataOut.'<li class="cataLI liTouch" data-cId="'.$ci.'">'.$ciCt.'</li>';
			}else{
				$cataOut=$cataOut.'<li class="cataLI liStart" data-cId="'.$ci.'">'.$ciCt.'</li>';
			}
		}
		$cataOut=$cataOut.'</ol><div id="fClrCata" class="fClr">Reset</div></dd>';
		
// man information		
		foreach ($collection as $prodm){
			$mid=$prodm->getManufacturer();
			$mnm=$prodm->getAttributeText('manufacturer');
			$manuInfo[$mid]=$mnm;
		}
		$pFilt=trim($session->getData('solrFilter-man'));
		$manuOut='<dt>Manufacturer</dt><dd><ol id="manuOL">';
		foreach($manuInfo as $mi=>$miCt){
			if($pFilt == $miCt){
				$manuOut=$manuOut.'<li class="manuLI liTouch" data-id="'.$mi.'">'.$miCt.'</li>';
			}else{
				$manuOut=$manuOut.'<li class="manuLI liStart" data-id="'.$mi.'">'.$miCt.'</li>';
			}
		}
		$manuOut=$manuOut.'</ol><div id="fClrManu" class="fClr">Reset</div></dd>';
		
		 echo($priceOut.$cataOut.$manuOut);
*/		
//		if($type=='price'){$filterBack=Mage::helper('solr')->searchPrice($collection);}
//		if($type=='manu'){$filterBack=Mage::helper('solr')->searchManu($collection);}
//		if($type=='cata'){$filterBack=Mage::helper('solr')->searchCata($collection);}
//		if($type=='sug'){$filterBack=Mage::helper('solr')->searchSug($term);}
//		if($type=='prod'){$filterBack=Mage::helper('solr')->prodOut($collection);}
//		if($filterBack){
//			echo($filterBack);
//		}else{
//			return "Filter Error";
//		}
//	}
}