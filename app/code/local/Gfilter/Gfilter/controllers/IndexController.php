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
 * @category   Gfilter
 * @package    Gfilter_Gfilter
 * @author     KTS Web Team <eric.gould@etoolsrus.com>
 */

/**
 * 
 * @author EricG
 *
 */
class Gfilter_Gfilter_IndexController extends Mage_Core_Controller_Front_Action{
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
		if($_POST['JSONin']){
			$this->filterRes($_POST['JSONin'],$_POST['pCol']);
		}
		
		if($_REQUEST['cartinator']=='add'){
			$this->cartinator($_REQUEST['pId']);
		}
		
		if(!$_REQUEST && !$_POST && !$_GET){
			echo "<h3>Public Methods in gfilter</h3>";
			foreach(get_class_methods($this) as $cm){
				$reflect = new ReflectionMethod($this,$cm);
				if($reflect->isPublic()){echo $cm.'<br/>';}}}}
		
		

	public function nsAction(){echo "Gfilter_Gfilter";}
	
	public function cartinator($pId){
		$item = $pId;
		$cart = Mage::getSingleton('checkout/cart');
		$product = Mage::getModel('catalog/product')
		->setStoreId(Mage::app()->getStore()->getId())
		->load($pId);
		$cart->addProduct($product,1);
		$cart->save();
		Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
		return 'Cart Updated';
	}
	
	
	public function filterRes($JSONin,$pCol="n/a"){
		$ji=json_decode($JSONin); 
		
		$pc=json_decode($pCol);	
// convert JSON object to array
			if(is_object($ji)){
				$ji = get_object_vars($ji);
			}else{
				$ji1 = $ji;
			}
						
			//build filter result output	
			$pgCol=$pCol; //cata prod id's
					
			//load collection of id's from cata
			$prodByCat= Mage::getResourceModel('catalog/product_collection')
				->addIdFilter($pgCol)
				->addAttributeToSelect('*');
				foreach($ji as $ji1=>$ji2){
				$prodByCat->addAttributeToFilter($ji1,$ji2);
				}
			//$prodByCat->load();
			//$this->setProductCollection($prodByCat);		
			$resCount=count($prodByCat);
			if($resCount > 0){
				print_r('<div id="prodList"><ol>');
				//pager init
				//$this->getMode('list');
		//		echo $this->getToolbarHtml();
				$_iterator = 0;
				
				echo ($pager);
				foreach($prodByCat as $prod){
					print_r('<li>');
					$pClass			= str_replace('"','',$prod->getName());							// clean product name to be used as class for selector in dialog functionallity.
					$name			= $prod->getName();												// get product name
					$desc			= $prod->getDescription();										// get product description
					$sDesc			= $prod->getShortDescription();									// get product short description
					//Product detail listing
					$prodAttCol = Mage::getResourceModel('eav/entity_attribute_collection')
					->setEntityTypeFilter(10)
					->getData();
					$attStr="";
					foreach($prodAttCol as $attCol){
						if($prod->getResource()->getAttribute($attCol['attribute_code'])->getFrontend()->getValue($prod)!=null && $attCol['frontend_label']!=null && $attCol['used_in_product_listing']==1 && $attCol['is_visible_on_front']==1){
							$attStr =$attStr.'<li>'.$attCol['frontend_label'].':'.$prod->getResource()->getAttribute($attCol['attribute_code'])->getFrontend()->getValue($prod).'</li>';
						}}
					$included		= $prod->getIncluded();											// get what is included with product                                               
					$setLocation 	= $this->getRequest()->getOriginalPathInfo(); 					// parent page location, depricatated, test then take out or add actual location of parent.					
					$urlKey 		= $prod->getUrl_key().'.html';									// get URL Key from attributes
					$price			= '$'.number_format($prod->getPrice(),2);						// get and format price
					$lgPict			= Mage::helper('catalog/image')->init($prod,'image');			// get pict, just get large image and resize as needed via resize() function
					$_helperCart 	= Mage::helper('checkout/cart')->getAddUrl($prod);				// cart helper, depricated, test then take out. functionality replaced with ajax method.
					$_helperComp 	= Mage::helper('catalog/product_compare')->getAddUrl($prod);	// compare helper, depricated, test then take out. functionality replaced with ajax method.
//TODO: test and take out dep items from above.
						
					$searchListing='	<div id="'.$prod->getId().'b" class="'.$pClass.'">
									<div id="searchListing">
										<div id="slTarget1" data-me="'.$prod->getId().'b" class="'.$pClass.'">
											<div id="slImg"><img src="'.$lgPict->resize(125).'"></div>					
											<div id="slTable">
												<span class="slTitle">'.$name.'</span><br/>
												<span class="slDesc">'.$desc.'</span>
											</div>
										</div>
										<div id="slAction">
											<span class="price lostingPrice">'.$price.'</span>
											<p id="cartBut">
												<button type="button" id="listLink" data-link="'.$_helperCart.'" title="'.$this->__('Add to Cart') .'" class="addToCart button btn-cart" ><span><span>'.$this->__('Add to Cart').'</span></span></button>
											</p>							
										</div>
									</div></div>
			';
	
					$dialog='	<div id="dialog" class="'.$prod->getId().'b">
							<table id="diaTable1">
								<tr id="diaTableTop"><td id="diaTable1Img"><img src="'.$lgPict->resize(225).'"></td><td id="diaTable1Data">
										<div class="dataTabs">
												<ul>
													<li><a href="#tab-1">Product Information</a></li>
													<li><a href="#tab-2">Product Details</a></li>
													<li><a href="#tab-3">In The Box</a></li>
												</ul>
													<div id="tab-1">
													<p>'.$sDesc.'</p>
													</div>
													<div id="tab-2">
													<p>'.$attStr.'</p>
													</div>
												 	<div id="tab-3">
													<p><ol>'.$included.'</ol></p>
													</div>
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
				
?>				
				<script type="text/javascript">
				<!--
				jQuery(document).ready(function($){
				//add to cart action
				/*$('.addToCart').click(function(){
					var cartUrl = $(this).data('link');
					var cartIt = $.post(cartUrl);
					cartIt.done(function(data){
						location.reload();
					});
				});
				*/				
				
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
							width: 665,
							height: 300,
							show:{
								effect: "blind",
								duration: 1000
							},
							hide: {
								effect: "explode",
								duration: 1000
							}
						});
						$('body').delegate('#slTarget1','click',function(){
							var myid='.'+$(this).data('me');
							alert(myid);
							var myTitle=$(this).attr('class');
							if($(myid).dialog('isOpen')==false){
								$(myid).dialog('open');
								$(myid).dialog('option','title',myTitle);
								$('.dataTabs').tabs();
							}
						});

/*	
$('body').on('click','#slTarget','',function(){
	//alert('fire');
	var myid='.'+$(this).data('me');
	var myTitle=$(this).attr('class');
	if($(myid).dialog('isOpen')==false){
		$(myid).dialog('open');
		$(myid).dialog('option','title',myTitle);
		$('.dataTabs').tabs();
	}							
});						
*/										
				});
								//-->
								</script>
								<?php 				
				
			}else{
				echo '<div id="noneFound">We did not find any results for the current filter combination.<br/> Please select another filter option.';
			}
	}

	public function getKtsAttribute($attIn="all"){
		$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
		
		foreach ($attributeSetCollection as $id=>$attributeSet) {
			$entityTypeId = $attributeSet->getEntityTypeId();
			$name = $attributeSet->getAttributeSetName();
			$attOuts[$name]=$id;
		}
		var_dump($attOuts);
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
			
		var_dump($attribute->getSource()->getAllOptions(false));
		
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
	
	public function starter(){
		// get all attributes
		$prAt = Mage::getResourceModel('catalog/product_attribute_collection');
		foreach($prAt as $pa1){
			$atCode=$pa1->getAttribute_code();
			$atLabel=$pa1->getFrontend_label();
			if( strpos($atCode,'ct_')=== 0){
				if(array_key_exists($atCode,$accArr)===false){
					$accArr[$atCode]=$atLabel;
				}
			}
		}
		var_dump($accArr);
	}
	
	public function getFilterData($fCol){
		return count($fCol);
	}

}
?>