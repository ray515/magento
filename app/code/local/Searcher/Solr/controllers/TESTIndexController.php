<?php
/**
 * Magento
 * 
 * @category    Mage
 * @package     Mage_Page
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Searcher Solr IndexController
 *
 * @category   Searcher
 * @package    Searcher_Solr
 * @author     KTS Web Team <eric.gould@etoolsrus.com>
 */

/**
 * 
 * @author EricG
 *
 */
class Searcher_Solr_IndexController extends Mage_Core_Controller_Front_Action{
	/**
	 * @name indexAction
	 * @param qRec='search term'
	 * @return array collection of results from solr search
	 * @param sku=sku 
	 * @param nova=nova 
	 */
	public function indexAction(){
		if($_REQUEST['test']||$_POST['test']){print_r("<h1>TRU Solr Tools.</h1>");}
		//$this->loadLayout();
		//$this->renderLayout();
		if($_REQUEST['qRec'] || $_POST['qRec']){
			$this->searchRes($_REQUEST['qRec']);
		}
		if($_REQUEST['sug']){
			$this->searchMage1($_REQUEST['sug']);
		}
		if($_REQUEST['sku']){
			$this->getCataIds($_REQUEST['sDir']);
		}
		if($_POST['sug1']){
			$this->searchMage($_POST['sug1']);
		}
		if($_REQUEST['nova']){
			$this->superNova($_REQUEST['sDir']);
		}
		if($_POST['ajax']){
			echo $this->ajaxRes($_POST['qRec'],$_POST['sDir']);
		}
		if($_REQUEST['const']){
			$this->conTest();
		}
		if($_REQUEST['atSet']){
			$this->getKtsAttribute($_REQUEST['atSet']);
		}
		if($_REQUEST['atManu']){
			$this->getManuList();
		}		
	}

// constants
	const SURL='http://65.60.97.68:8983/solr/KTS1';

	public function nsAction(){
		echo "Searcher_Solr";
	}
	
	public function getCataIds($sDir){
		print_r('<h3>Update Complete</h3>');
		$prodCol=Mage::getModel('catalog/product')->getCollection();
		$prodCol->addAttributeToSelect('*');
		echo "<hr/>";
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
			//var_dump($jOut);
		}
		//echo $jOut;
		echo '<br/><br/>';
		echo "<hr/>";
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

	public function searchMage($sRec){
		$sugStr					= urlencode($sRec);
		$url					= Mage::helper('solr')->sURL().'suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch						= curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output					= curl_exec($ch);
		$result					= json_decode($output, TRUE);
		$res					= $result['spellcheck']['suggestions'][1]['suggestion'];
		$out					= '<ol>';
		foreach($res as $res1){
			$out=$out.'<li>'.$res1.'</li>';
		}
		$out					= $out.'</ol>';
		echo $out;		
	}
	
	public function searchMage1($sugStr){
		//echo("found");
		$sugStr=urlencode($sugStr);
		$url=Mage::helper('solr')->sURL().'suggest?wt=json&q='.$sugStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);

		$res=$result['spellcheck']['suggestions'][1]['suggestion'];
		$jCt=0;
		foreach($res as $res1){
			$data[]=array("label"=>$res1);
			$jCt++;
		}
		echo json_encode($data);
	}
	
	public function searchRes2($term){
		$resStr=urlencode($term);
		$url=Mage::helper('solr')->sURL().'select?wt=json&q='.$resStr;
		// using curl method
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$output=curl_exec($ch);
		$result=json_decode($output, TRUE);
		
		foreach($result['response']['docs'] as $rOut1){
			$sID[] = $rOut1['sku'];
		}
		
		//var_dump($sID);
	}
	
	public function searchColl($term){
		
	}
	
	public function searchRes($term){
		
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
		$this->collection=$collection;
		print_r('<div id="fList">');
		
//		echo '<div id="priceReplace">'.Mage::helper('solr')->searchPrice($collection).'</div>';
//		echo '<div id="cataReplace">'.Mage::helper('solr')->searchCata($collection).'</div>';
//		echo '<div id="manuReplace">'.Mage::helper('solr')->searchManu($collection).'</div>';
		echo '<div id="sugReplace">'.Mage::helper('solr')->searchMage($resStr).'</div>';

		echo '<hr/>';
		$url=urlencode($solrPg.'catalogsearch/result?qRec='.$resStr);
		Mage::getSingleton('checkout/session')->setData('continue_shopping_url', $url);
		foreach($collection as $cid1){
			$cata1=$cid1->getCategoryIds();
			$cOut="";
			foreach($cata1 as $cata){			
				$_cat=Mage::getModel('catalog/category')->load($cata);		
				$cOutId=$cOut.$_cat->getId();
				if($cOutId != 3){
				$cOut=$cOut.$_cat->getName().',';
						if(array_key_exists($cOut,$catArr)){
							$catArr[$cOut]++;
						}else{
							$catArr[$cOut]=1;
						}
				}else{}
			}
		}
		print_r('<div id="prodList"><ol>');
		foreach($collection as $prod){
			print_r('<li>');
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
				//var_dump($tst1);echo "<br/><br/>";
				if($prod->getResource()->getAttribute($tst1['attribute_code'])->getFrontend()->getValue($prod)!=null && $tst1['frontend_label']!=null && $tst1['used_in_product_listing']==1 && $tst1['is_visible_on_front']==1){
					$attStr =$attStr.'<li>'.$tst1['frontend_label'].':'.$prod->getResource()->getAttribute($tst1['attribute_code'])->getFrontend()->getValue($prod).'</li>';
				}
			}
			$included=$prod->getIncluded();
			$qParam=array('qRec'=>$_GET['qRec']);
			$setLocation = Mage::getUrl('*/*',array(_query=>$qParam));
			$urlKey = $prod->getUrl_key().'.html';
			$price='$'.number_format($prod->getPrice(),2);
			$lgPict=Mage::helper('catalog/image')->init($prod,'image');
			$_helperCart = Mage::helper('checkout/cart')->getAddUrl($prod);
			$_helperComp = Mage::helper('catalog/product_compare')->getAddUrl($prod);
			
			$searchListing='	<div id="'.$prod->getId().'o" class="'.$pClass.'">
									<div id="searchListing">
										<div id="slImg"><img src="'.$lgPict->resize(125).'"></div>
										<div id="slTable">
											<table id="listTable">
											<caption>'.$name.'</caption><tr><td class="col1">'.$desc.'<br/></td>
												<td class="col2 regular-price">
													<span class="price listingPrice">'.$price.'</span>
															<p id="cartBut">
															<button type="button" id="listLink" data-link="'.$_helperCart.'" title="'.$this->__('Add to Cart') .'" class="addToCart button btn-cart" ><span><span>'.$this->__('Add to Cart').'</span></span></button></p></td></tr>
											</table>
										</div>
									</div></div>
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
													<div id="tab-1"><p>'.$sDesc.'</p></div>
													<div id="tab-2"><p>'.$attStr.'</p></div>
												 	<div id="tab-3"><p><ol>'.$included.'</ol></p></div>
										</div>										
										</td><tr>
								<tr id="diaTableBot"><td id="diaTable1Comp"><a href="'.Mage::getUrl('/').$urlKey.'">View Product Page</a></td><td id="diaTable1Action">
										<div id=diaTableActionPrice><input type=hidden class=addToCompData value="'.$_helperComp.'"><button type="button" data-link="'.$_helperComp.'" title="'.$this->__('Compare') .'" class="addToComp button btn-cart"><span><span class="price listingPrice">'.$this->__('Compare').'</span></span></button></div>   
										<div id=diaTableActionPrice><button type="button" data-link="'.$_helperCart.'" title="'.$this->__('Add to Cart') .'" class="addToCart button btn-cart" ><span><span class="price listingPrice">'.$price.' '.$this->__('Add to Cart').'</span></span></button></div></td></tr>
							</table>
						</div>			';
			print_r($searchListing.$dialog);		
			print_r('</li>');
		
		}
	print_r('</ol></div>'); //close prodList
	print_r('</div>'); //close fList
	print_r('<hr/>');
	print_r('<div id="holder"></div>');
//	echo($this->sFilter($collection));
	$solrPg=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
?>
		<script type="text/javascript">
<!--
jQuery(document).ready(function($){
//init fSel
/*	if(!fSel){
		var fSel = new Array();
		fSel[0]="";fSel[1]="";fSel[2]="";
	}*/
//init cookies
/*	if(!$.cookie('KTS_ALD')){
		$.cookie('KTS_ALD','0');
	}
	if($.cookie('KTS_PS')){
		//replace
		$('#pSearch').html($.cookie('KTS_PS'));
	}else{
		//init
		$.cookie('KTS_PS','0');
	}
	if(!$.cookie('KTS_CC1')){
		$.cookie('KTS_CC1',fSel.toString());
	}*/

//add to cart action
	$('.addToCart').click(function(){
		var cartUrl = $(this).data('link');
		var cartIt = $.post(cartUrl);
			cartIt.done(function(data){
				location.reload();
			});
	});
		

//compare action	
	$('.addToComp').click(function(){
		var compUrl = $(this).data('link');
		var compIt = $.post(compUrl);
			compIt.done(function(data){
				location.reload();
			});
	});
	
//prod info dialog
	$('#prodList ol>li #dialog').dialog({
			autoOpen: false,
			width:    665,
			height:   300,
			modal:    true,
			show:	  {
					    effect: 	"blind",
					    duration: 	1000
					  },
			hide: 	  {
					    effect: 	"explode",
				  	    duration: 	1000
					  }
	});

	var stopDia = false;
	$('#prodList ol>li>div #listLink').click( function(){
		return stopDia = true;
	});
	
	$('#prodList ol>li>div').click( function(){	
		if(stopDia == true){
			return;
		}else{
			var myid='.'+$(this).attr('id');
			var myTitle=$(this).attr('class');
			if($(myid).dialog('isOpen')==false){
				$(myid).dialog('open');
				$(myid).dialog('option','title',myTitle);
				$('.dataTabs').tabs();
			};
		};
	});
	
//hide helper divs
//	$('#cataReplace').hide();
//	$('#priceReplace').hide();
//	$('#manuReplace').hide();
	$('#sugReplace').hide();
	$('#pSearch').hide();
	$('#filterTitle').html('Shopping Options<span class="fClr"> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Reset All</span>');

//popular search section and action
	$('#deepSearch>#deepSearchTitle').html('But wait... there\s more...');
	$('#deepTitle').html('Popular searches with <br/> '+$('#bodySearch').val());
	$('#deepRes').html($('#sugReplace').html());
	$('#deepRes>ol>li').click( function(){	
		$('#bodySearch').val('"'+$(this).html()+'"');
		$('#search').val('"'+$(this).html()+'"');sps();
		$.cookie('KTS_CC1',',,');
		$.cookie('KTS_ALD',0);
		sGo();
	});
	
//on load check cookie for selections
//	if($.cookie('KTS_ALD')==0){
//		fSelOnLoad();
//	}
	
// clear previous selection, get new selection, clean data, save data to cookie, update selection style for all filters. 	
/*	$('#idCategory ol > li').click( function(){
		$(this).siblings().removeClass('liTouch').addClass('liStart');
		var cataTxt = $(this).html();
		var ClCoTxt = cleanTxt(cataTxt);
		fSel=$.cookie('KTS_CC1').split(',');
		fSel[0]=escape(ClCoTxt);
		$.cookie('KTS_CC1',fSel.toString());			
		$(this).removeClass('liStart').addClass('liTouch');
		$.cookie('KTS_ALD',1);
		sGo();	
	});  

	$('#idPrice ol > li').click( function(){
		$(this).siblings().removeClass('liTouch').addClass('liStart');
		var priceTxt = $(this).html();
		var ClCoTxt = cleanTxt(priceTxt);
		fSel=$.cookie('KTS_CC1').split(',');
		fSel[1]=escape(ClCoTxt);
		$.cookie('KTS_CC1',fSel.toString());		
		$(this).removeClass('liStart').addClass('liTouch');
		$.cookie('KTS_ALD',1);
		sGo();
	});

	$('#idManufacturer ol > li').click( function(){
		$(this).siblings().removeClass('liTouch').addClass('liStart');
		var manuTxt = $(this).html();
		var ClCoTxt = cleanTxt(manuTxt);
		fSel=$.cookie('KTS_CC1').split(',');
		fSel[2]=escape(ClCoTxt);
		$.cookie('KTS_CC1',fSel.toString());			
		$(this).removeClass('liStart').addClass('liTouch');
		$.cookie('KTS_ALD',1);
		sGo();
	});*/

//reset functionality - replace cookie data, reset style setting and go.
/*	$('#idCategory #fClrCata').click( function(){
		fSel=$.cookie('KTS_CC1').split(',');fSel[0]='';$.cookie('KTS_CC1',fSel.toString());
		$('#idCategory ol > li').siblings().andSelf().removeClass('liTouch').addClass('liStart');
		$.cookie('KTS_ALD',1);
		sGo();
	});
	
	$('#idPrice #fClrPrice').click( function(){
		fSel=$.cookie('KTS_CC1').split(',');fSel[1]='';$.cookie('KTS_CC1',fSel.toString());
		$('#idPrice ol > li').siblings().andSelf().removeClass('liTouch').addClass('liStart');
		$.cookie('KTS_ALD',1);
		sGo();
	});

	$('#idManufacturer #fClrManu').click( function(){
		fSel=$.cookie('KTS_CC1').split(',');fSel[2]='';$.cookie('KTS_CC1',fSel.toString());
		$('#idManufacturer ol > li').siblings().andSelf().removeClass('liTouch').addClass('liStart');
		$.cookie('KTS_ALD',1);
		sGo();
	});

	$('.block-subtitle span').click( function(){
		$.cookie('KTS_CC1',',,');
		$('* li').removeClass('liTouch').addClass('liStart');
		$.cookie('KTS_ALD',1);
		sGo();
	});
*/
//Previous Search Term Action
//	$('#deepPrev>ol>li').click( function(){
//		$('#bodySearch').val('"'+$(this).html()+'"');
//		$('#search').val('"'+$(this).html()+'"');
//		$.cookie('KTS_CC1',',,');
//		$.cookie('KTS_ALD',1);
//		sGo();
//	});
	
//function fSelOnLoad(){
//read cookies
//	var ald = $.cookie('KTS_ALD');
//	var fSel1 = $.cookie('KTS_CC1');
//	fSel1 = fSel1.split(',');
//	fsTemp1 = fSel1[1].split('.');
//	fSel1[1]='$'+fsTemp1[0]+'.00'; //cover the money stuffs
	
//replace filter data on search start only
//	if(ald == '0'){
//		$('#idCategory').html($('#cataReplace').html()).show();
//		$('#idPrice').html($('#priceReplace').html()).show();
//		$('#idManufacturer').html($('#manuReplace').html()).show();
//	}
	
//select-ness - clear all selections, select items that match cookie data with selection class.
//	$('*').removeClass('liTouch');
//	$('#narrow-by-list li:contains("'+unescape(fSel1[0])+'")').addClass('liTouch');
//	$('#narrow-by-list li:contains("'+unescape(fSel1[1])+'")').addClass('liTouch');
//	$('#narrow-by-list li:contains("'+unescape(fSel1[2])+'")').addClass('liTouch');
//	sp();
//}

//Clean up text by removing $ an (x) from selection - smooth regex action ;)
/*
function cleanTxt(ttc){
	tIn=ttc.replace(/(?:\$| *\([^)]*\) *)/g, "");
	return tIn;
}

function deDup(arr) {
	  var i,len=arr.length,out=[],obj={};for (i=0;i<len;i++) {  obj[arr[i]]=0;  } for (i in obj) { out.push(i);  } return out;
}
*/
/*
function sGo(){
			var outDone = "";
		// read cookie
			var cData=unescape($.cookie('KTS_CC1'));
			var cd1 = cData.split(',');

			if(cd1[1].indexOf(' or more')!=false){  cd1[1]=cd1[1].replace('or more','TO 1000000.00');	 }
			outDone = unescape($('#bodySearch').val());
			if(cd1[0] != undefined && cd1[0] != ""){outDone=outDone+' AND cat:"'+cd1[0]+'"';}else{}
			if(cd1[1] != undefined && cd1[1] != ""){
				outDone=outDone+' AND price:['+cd1[1].toUpperCase()+']';
				}else{}
			if(cd1[2] != undefined && cd1[2] != ""){outDone=outDone+' AND '+cd1[2]; }else{}

		// and... make it so, number one.
			//var getit = $.post('<?php echo($solrPg); ?>solr/index/index/',{qRec:outDone});
			var g1 = '<?php echo($solrPg); ?>';
			var getit1 = $.post(g1+'solr/index/index/',{qRec:outDone});
				getit1.done(function(data){
					$('.ui-dialog').remove();
					$('#tt').val('sGo');
					$('#solrBurn').html(data);
				});
				getit1.fail(function(data){
					alert("FAIL: "+data);
				});	
				
}


	function sps(tar){
		var tar1 = $('#bodySearch').val();
		if($('#pSearch').html()==""){
			$('#pSearch').html(tar1);
		}else{
			var sTerm = tar1.replace('"','').replace('"','');
			var psTemp=$('#pSearch').html()+','+sTerm;
			var psDeDup = psTemp.split(',');
			psDeDup = deDup(psDeDup);
			psTemp = psDeDup.toString();
			$('#pSearch').html(psTemp);
		}
		sp(); return true;
	}

	function sp(){
		if($('#pSearch').html() != "" && $('#deepPrev').length == 0){
			var i;
			$('#deepSearch').append('<dt id="deepPrevTitle">Your Search Terms</dt><dd id="deepPrev"><ol></ol></dd>');
			var psDat = $('#pSearch').html().split(',');
			var psOut = "";
			for(i=0;i<psDat.length;i++){  psOut=psOut+"<li>"+psDat[i]+"</li>";  }
			$('#deepPrev ol').html(psOut);	
			$('#pSearch').html(psDat.toString());
			$.cookie('KTS_PS',$('#pSearch').html());
		}else if($('#pSearch').length != 0){
			var psDat = $('#pSearch').html().split(',');
			psDat=deDup(psDat);
			var psOut = "";
			for(i=0;i<psDat.length;i++){  psOut=psOut+"<li>"+psDat[i]+"</li>";  }
			$('#deepPrev ol').html(psOut);
			$('#pSearch').html(psDat.toString());
			$.cookie('KTS_PS',$('#pSearch').html());	
		}else{}
	}
	*/
});
	
//-->
</script>
<?php 
	}

	public function sFilter($tCol){
		$sfOut='collection count: '.count($tCol);
		
		return $sfOut;
	}
	
	public function getKtsAttribute($attIn="all"){
		$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
		
		foreach ($attributeSetCollection as $id=>$attributeSet) {
			$entityTypeId = $attributeSet->getEntityTypeId();
			$name = $attributeSet->getAttributeSetName();
			$attOuts[$name]=$id;
		}
		//var_dump($attOuts);
		if(array_key_exists($attIn,$attOuts)){
			$attOut=$attOuts[$attIn];
		}else{
			$attOut=73;	
		}
	}

	public function getManuList($arg_attribute, $arg_value){
		$attribute_model        = Mage::getModel('eav/entity_attribute');
	
		$attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
		$attribute              = $attribute_model->load($attribute_code);
		
		if(!$this->attributeValueExists($arg_attribute, $arg_value))
		{
			$value['option'] = array($arg_value,$arg_value);
			$result = array('value' => $value);
			$attribute->setData('option',$result);
			$attribute->save();
		}
		
		$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
		$attribute_table        = $attribute_options_model->setAttribute($attribute);
		$options                = $attribute_options_model->getAllOptions(false);
		
		foreach($options as $option)
		{
			if ($option['label'] == $arg_value)
			{
				return $option['value'];
			}
		}
		
		$attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'manufacturer');
		if ($attribute->usesSource()) {
			$options = $attribute->getSource()->getAllOptions(false);
		}
		$attribute->setSource()->addData(array('label'=>'tester'));
		$attribute->save();
			
		//var_dump($attribute->getSource()->getAllOptions(false));
		
		
		return false;
	}
	public function attributeValueExists($arg_attribute, $arg_value)
	{
		$attribute_model        = Mage::getModel('eav/entity_attribute');
		$attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
	
		$attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
		$attribute              = $attribute_model->load($attribute_code);
	
		$attribute_table        = $attribute_options_model->setAttribute($attribute);
		$options                = $attribute_options_model->getAllOptions(false);
	
		foreach($options as $option)
		{
			if ($option['label'] == $arg_value)
			{
				return $option['value'];
			}
		}
	
		return false;
	}

}
?>