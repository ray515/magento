<?php

class TRU_ProdUp1_Adminhtml_Produp1Controller extends Mage_Adminhtml_Controller_action
{

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('produp1/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
		     ->renderLayout();
	}

	public function editAction() {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('produp1/produp1')->load($id);

		if ($model->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$model->setData($data);
			}

			Mage::register('produp1_data', $model);

			$this->loadLayout();
			$this->_setActiveMenu('produp1/items');

			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
			$this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

			$this->_addContent($this->getLayout()->createBlock('produp1/adminhtml_produp1_edit'))
			     ->_addLeft($this->getLayout()->createBlock('produp1/adminhtml_produp1_edit_tabs'));

			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('produp1')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction() {
		if ($data = $this->getRequest()->getPost()) {
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {	
                                        /* Starting upload */	
					$uploader = new Varien_File_Uploader('filename');
					
					// Any extention would work
	           		$uploader->setAllowedExtensions(array('csv','jpg','jpeg','gif','png'));
					$uploader->setAllowRenameFiles(false);
					
					// Set the file upload mode 
					// false -> get the file directly in the specified folder
					// true -> get the file in the product like folders 
					//	(file.jpg will go in something like /media/f/i/file.jpg)
					$uploader->setFilesDispersion(false);
							
					// We set media as the upload dir
					$path = Mage::getBaseDir('media') . DS ;
                                        $path = $path.'ProdUp';
					$uploader->save($path, $_FILES['filename']['name'] );
					
				} catch (Exception $e) {
		      
		        }
	        
		        //Save FILE name in DB and rename TITLE to file name
	  			$data['filename'] = $_FILES['filename']['name'];
                                $data['title'] = $_FILES['filename']['name'];
			}
	  			
	  			
			$model = Mage::getModel('produp1/produp1');		
			$model->setData($data)
				->setId($this->getRequest()->getParam('id'));
			
			try {
				if ($model->getCreatedTime == NULL || $model->getUpdateTime() == NULL) {
					$model->setCreatedTime(now())
						->setUpdateTime(now());
				} else {
					$model->setUpdateTime(now());
				}	
				
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('produp1')->__('Item was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('produp1')->__('Unable to find item to save'));
        $this->_redirect('*/*/');
	}
 
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0 ) {
			try {
				$model = Mage::getModel('produp1/produp1');
				 
				$model->setId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('Item was successfully deleted'));
				$this->_redirect('*/*/');
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

		public function getAtSet($aType){
		$product = Mage::getModel('catalog/product');
		$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
		->setEntityTypeFilter($product->getResource()->getTypeId())
		->addFieldToFilter('attribute_code', $aType); //can be changed to any attribute
		$attribute = $attributes->getFirstItem()->setEntity($product->getResource());
		$manufacturers = $attribute->getSource()->getAllOptions(false);
		
		return $manufacturers;
	}
	
	protected function parseIt($filename=''){
		if(!file_exists($filename) || !is_readable($filename)){
			echo "file name error!";
			return FALSE;
		}
		
		$header = NULL;
		$data = array();
		if(($handle = fopen($filename, 'r')) !== FALSE){
			while(($row = fgetcsv($handle, 0,',')) !==FALSE){
				if(!$header){
					$header = $row;
				}else{
					$data[] = array_combine($header, $row);
				}
			}
			fClose($handle);
		}
		return $data;
	}
	
	public function processAction() {		
		if( $this->getRequest()->getParam('produp1') > 0 ) {
				// get list of vendors
				$product = Mage::getModel('catalog/product');
				$attributes = Mage::getResourceModel('eav/entity_attribute_collection')
				->setEntityTypeFilter($product->getResource()->getTypeId())
				->addFieldToFilter('attribute_code', 'manufacturer'); //can be changed to any attribute
				$attribute = $attributes->getFirstItem()->setEntity($product->getResource());
				$manufacturers = $attribute->getSource()->getAllOptions(false);
			
			try {
				$id=$this->getRequest()->getParam('produp1');
                                $model = Mage::getModel('produp1/produp1')
				 ->Load($id);
                                
                                if($model->getStatus()=="1"){
                                    $path = Mage::getBaseDir('media') . DS ;
                                    $path = $path.'ProdUp/';
                                    $file=$model->getFilename();
									$data =  $this->parseIt($path.$file);
                                    //$cpDat = fopen($path.$file, "r");
                                    //$fDat = fgetcsv($cpDat, 0, ",");

                                    for($i=1; $i < count($data); $i++) {
                                            $column[$i] = $data[$i];
                                        }
    //TODO: Error control for upload
									for($i=0;$i<count($data); $i++){
                                    //while (($data = fgetcsv($cpDat, 0, ",")) !== FALSE) {
									
//build new product
                                        $newproduct = Mage::getModel('catalog/product');
                                        
                                        $productId = $newproduct->getIdBySku($data[$i]['sku']);   
// get proper attribute set id
                                		$attriSet = $data[$i]['_attribute_set'];
                                		if($attriSet){
                                			$attributeSetCollection = Mage::getResourceModel('eav/entity_attribute_set_collection') ->load();
                                			
                                			foreach ($attributeSetCollection as $id=>$attributeSet) {
                                				$entityTypeId = $attributeSet->getEntityTypeId();
                                				$name = $attributeSet->getAttributeSetName();
                                				$attOuts[$name]=$id;
                                			}
                                			if(array_key_exists($attriSet,$attOuts)){
                                				$attOut=$attOuts[$attriSet];
                                			}else{
                                				$attOut=73; //set to attribute set "all"
                                			}
                                			echo "attribute set: ".$attOut."<br/>";                                			
                                		}  

                                		if($data[$i]['use_alum']){
                                			$alumIn=strtolower($data[$i]['use_alum']);
                                			if($alumIn=="good"){
                                				$alumOut=162;//good
                                			}elseif($alumIn=='best'){
                                				$alumOut=163;//best
                                			}else{
                                				$alumOut=161;//N/A
                                			}
                                		}
                                		//csteel
                                		if($data[$i]['use_csteel']){
                                			$csteelIn=strtolower($data[$i]['use_csteel']);
                                			if($csteelIn=="good"){
                                				$csteelOut=165;//good
                                			}elseif($csteelIn=='best'){
                                				$csteelOut=166;//best
                                			}else{
                                				$csteelOut=164;//N/A
                                			}
                                		}
                                		//asteel
                                		if($data[$i]['use_asteel']){
                                			$asteelIn=strtolower($data[$i]['use_asteel']);
                                			if($asteelIn=="good"){
                                				$asteelOut=168;//good
                                			}elseif($asteelIn=='best'){
                                				$asteelOut=169;//best
                                			}else{
                                				$asteelOut=167;//N/A
                                			}
                                		}
                                		//hsteel
                                		if($data[$i]['use_hsteel']){
                                			$hsteelIn=strtolower($data[$i]['use_hsteel']);
                                			if($hsteelIn=="good"){
                                				$hsteelOut=171;//good
                                			}elseif($hsteelIn=='best'){
                                				$hsteelOut=172;//best
                                			}else{
                                				$hsteelOut=170;//N/A
                                			}
                                		}
                                		//ssteel
                                		if($data[$i]['use_ssteel']){
                                			$ssteelIn=strtolower($data[$i]['use_ssteel']);
                                			if($ssteelIn=="good"){
                                				$ssteelOut=177;//good
                                			}elseif($ssteelIn=='best'){
                                				$ssteelOut=178;//best
                                			}else{
                                				$ssteelOut=176;//N/A
                                			}
                                		}
                                		//nickel
                                		if($data[$i]['use_nickel']){
                                			$nickelIn=strtolower($data[$i]['use_nickel']);
                                			if($nickelIn=="good"){
                                				$nickelOut=174;//good
                                			}elseif($nickelIn=='best'){
                                				$nickelOut=175;//best
                                			}else{
                                				$nickelOut=173;//N/A
                                			}
                                		}
                                		//ciron
                                		if($data[$i]['use_ciron']){
                                			$cironIn=strtolower($data[$i]['use_ciron']);
                                			if($cironIn=="good"){
                                				$cironOut=180;//good
                                			}elseif($cironIn=='best'){
                                				$cironOut=181;//best
                                			}else{
                                				$cironOut=179;//N/A
                                			}
                                		}
                                		//tit
                                		if($data[$i]['use_tit']){
                                			$titIn=strtolower($data[$i]['use_tit']);
                                			if($titIn=="good"){
                                				$titOut=183;//good
                                			}elseif($titIn=='best'){
                                				$titOut=184;//best
                                			}else{
                                				$titOut=182;//N/A
                                			}
                                		}
                                		
                                        if($productId) {
                                            $newproduct -> load( $productId );
                                           // if($data[$i]['sku']){$newproduct->setData('sku',$data[$i]['sku']);}
												if($data[$i]['_store']){$newproduct->setStore($data[$i]['_store']);}
												if($attOut){$newproduct->setAttributeSetId($attOut);}
												if($data[$i]['_type']){$newproduct->setTypeId($data[$i]['_type']);}
												if($data[$i]['_category']){$newproduct->setCategoryIds($data[$i]['_category']);}
												$newproduct->setWebsiteIDs(array(1));
												if($data[$i]['status']){$newproduct->setStatus($data[$i]['status']);}
												if($data[$i]['tax_class_id']){$newproduct->setTaxClassId($data[$i]['tax_class_id']);}
												if($data[$i]['visibility']){$newproduct->setvisibility($data[$i]['visibility']);}
												if($data[$i]['price']){$newproduct->setPrice($data[$i]['price']);}
												if($data[$i]['enable_googlecheckout']){$newproduct->setEnable_googlecheckout($data[$i]['enable_googlecheckout']);}
												if($data[$i]['meta_title']){$newproduct->setMeta_title($data[$i]['meta_title']);}
                                                if($data[$i]['meta_description']){$newproduct->setMeta_description($data[$i]['meta_description']);}
                                                if($data[$i]['name']){$newproduct->setName($data[$i]['name']);}
												if($data[$i]['description']){$newproduct->setData('description',$data[$i]['description']);}
                                                if($data[$i]['short_description']){$newproduct->setShortDescription($data[$i]['short_description']);}
												if($data[$i]['included']){$newproduct->setIncluded($data[$i]['included']);}
                                                if($data[$i]['in_depth']){$newproduct->setIn_depth($data[$i]['in_depth']);}
                                                if($data[$i]['name']){$newproduct->setData('name',$data[$i]['name']);}
												if($data[$i]['UPC']){$newproduct->setData('upc',$data[$i]['UPC']);}
												if($data[$i]['spec_accuracy']){$newproduct->setData('spec_accuracy',$data[$i]['spec_accuracy']);}
												if($data[$i]['spec_air_temp']){$newproduct->setData('spec_air_temp',$data[$i]['spec_air_temp']);}
												if($data[$i]['spec_air_volume']){$newproduct->setData('spec_air_volume',$data[$i]['spec_air_volume']);}
												if($data[$i]['spec_alumn_cutting_capct']){$newproduct->setData('spec_alumn_cutting_capct',$data[$i]['spec_alumn_cutting_capct']);}
												if($data[$i]['spec_amps']){$newproduct->setData('spec_amps',$data[$i]['spec_amps']);}
												if($data[$i]['spec_arbor_size']){$newproduct->setData('spec_arbor_size',$data[$i]['spec_arbor_size']);}
												if($data[$i]['spec_base_diameter']){$newproduct->setData('spec_base_diameter',$data[$i]['spec_base_diameter']);}
												if($data[$i]['spec_base_opening']){$newproduct->setData('spec_base_opening',$data[$i]['spec_base_opening']);}
												if($data[$i]['spec_battery']){$newproduct->setData('spec_battery',$data[$i]['spec_battery']);}
												if($data[$i]['spec_bevel_miter_capct']){$newproduct->setData('spec_bevel_miter_capct',$data[$i]['spec_bevel_miter_capct']);}
												if($data[$i]['spec_bevel_capacity']){$newproduct->setData('spec_bevel_capacity',$data[$i]['spec_bevel_capacity']);}
												if($data[$i]['spec_blade_side']){$newproduct->setData('spec_blade_side',$data[$i]['spec_blade_side']);}
												if($data[$i]['spec_blade_size']){$newproduct->setData('spec_blade_size',$data[$i]['spec_blade_size']);}
												if($data[$i]['spec_blows_min_bpm']){$newproduct->setData('spec_blows_min_bpm',$data[$i]['spec_blows_min_bpm']);}
												if($data[$i]['spec_capacity']){$newproduct->setData('spec_capacity',$data[$i]['spec_capacity']);}
												if($data[$i]['spec_capacity_45dgres']){$newproduct->setData('spec_capacity_45dgres',$data[$i]['spec_capacity_45dgres']);}
												if($data[$i]['spec_capacity_50dgres']){$newproduct->setData('spec_capacity_50dgres',$data[$i]['spec_capacity_50dgres']);}
												if($data[$i]['spec_capacity_90dgres']){$newproduct->setData('spec_capacity_90dgres',$data[$i]['spec_capacity_90dgres']);}
												if($data[$i]['spec_charge_time']){$newproduct->setData('spec_charge_time',$data[$i]['spec_charge_time']);}
												if($data[$i]['spec_chipping']){$newproduct->setData('spec_chipping',$data[$i]['spec_chipping']);}
												if($data[$i]['spec_chuck_size']){$newproduct->setData('spec_chuck_size',$data[$i]['spec_chuck_size']);}
												if($data[$i]['spec_chuck_type']){$newproduct->setData('spec_chuck_type',$data[$i]['spec_chuck_type']);}
												if($data[$i]['spec_clutch']){$newproduct->setData('spec_clutch',$data[$i]['spec_clutch']);}
												if($data[$i]['spec_clutch_settings']){$newproduct->setData('spec_clutch_settings',$data[$i]['spec_clutch_settings']);}
												if($data[$i]['spec_collet_capacity']){$newproduct->setData('spec_collet_capacity',$data[$i]['spec_collet_capacity']);}
												if($data[$i]['spec_compatible_with']){$newproduct->setData('spec_compatible_with',$data[$i]['spec_compatible_with']);}
												if($data[$i]['spec_concrete_drill_capacity']){$newproduct->setData('spec_concrete_drill_capacity',$data[$i]['spec_concrete_drill_capacity']);}
												if($data[$i]['spec_cord_length']){$newproduct->setData('spec_cord_length',$data[$i]['spec_cord_length']);}
												if($data[$i]['spec_cord_type']){$newproduct->setData('spec_cord_type',$data[$i]['spec_cord_type']);}
												if($data[$i]['spec_cutting_capct']){$newproduct->setData('spec_cutting_capct',$data[$i]['spec_cutting_capct']);}
												if($data[$i]['spec_depth_adj_range']){$newproduct->setData('spec_depth_adj_range',$data[$i]['spec_depth_adj_range']);}
												if($data[$i]['spec_diameter']){$newproduct->setData('spec_diameter',$data[$i]['spec_diameter']);}
												if($data[$i]['spec_dimensions']){$newproduct->setData('spec_dimensions',$data[$i]['spec_dimensions']);}
												if($data[$i]['spec_drive_type']){$newproduct->setData('spec_drive_type',$data[$i]['spec_drive_type']);}
												if($data[$i]['spec_drive_size']){$newproduct->setData('spec_drive_size',$data[$i]['spec_drive_size']);}
												if($data[$i]['spec_electric_brake']){$newproduct->setData('spec_electric_brake',$data[$i]['spec_electric_brake']);}
												if($data[$i]['spec_impact_energy']){$newproduct->setData('spec_impact_energy',$data[$i]['spec_impact_energy']);}
												if($data[$i]['spec_laser_diode']){$newproduct->setData('spec_laser_diode',$data[$i]['spec_laser_diode']);}
												if($data[$i]['spec_led_light']){$newproduct->setData('spec_led_light',$data[$i]['spec_led_light']);}
												if($data[$i]['spec_levlg_type_range']){$newproduct->setData('spec_levlg_type_range',$data[$i]['spec_levlg_type_range']);}
												if($data[$i]['spec_masonry_drill_capcty']){$newproduct->setData('spec_masonry_drill_capcty',$data[$i]['spec_masonry_drill_capcty']);}
												if($data[$i]['spec_material']){$newproduct->setData('spec_material',$data[$i]['spec_material']);}
												if($data[$i]['spec_metal_cutting_capct']){$newproduct->setData('spec_metal_cutting_capct',$data[$i]['spec_metal_cutting_capct']);}
												if($data[$i]['spec_mild_steel_cut_capct']){$newproduct->setData('spec_mild_steel_cut_capct',$data[$i]['spec_mild_steel_cut_capct']);}
												if($data[$i]['spec_miter_detents']){$newproduct->setData('spec_miter_detents',$data[$i]['spec_miter_detents']);}
												if($data[$i]['spec_model']){$newproduct->setData('spec_model',$data[$i]['spec_model']);}
												if($data[$i]['spec_motor_hp']){$newproduct->setData('spec_motor_hp',$data[$i]['spec_motor_hp']);}
												if($data[$i]['spec_mount_threadg']){$newproduct->setData('spec_mount_threadg',$data[$i]['spec_mount_threadg']);}
												if($data[$i]['spec_noise_level']){$newproduct->setData('spec_noise_level',$data[$i]['spec_noise_level']);}
												if($data[$i]['spec_no_load_speed']){$newproduct->setData('spec_no_load_speed',$data[$i]['spec_no_load_speed']);}
												if($data[$i]['spec_operation_temptr']){$newproduct->setData('spec_operation_temptr',$data[$i]['spec_operation_temptr']);}
												if($data[$i]['spec_orbit_diameter']){$newproduct->setData('spec_orbit_diameter',$data[$i]['spec_orbit_diameter']);}
												if($data[$i]['spec_oscillating_angle']){$newproduct->setData('spec_oscillating_angle',$data[$i]['spec_oscillating_angle']);}
												if($data[$i]['spec_pad_size']){$newproduct->setData('spec_pad_size',$data[$i]['spec_pad_size']);}
												if($data[$i]['spec_paper_size_type']){$newproduct->setData('spec_paper_size_type',$data[$i]['spec_paper_size_type']);}
												if($data[$i]['spec_planing_depth']){$newproduct->setData('spec_planing_depth',$data[$i]['spec_planing_depth']);}
												if($data[$i]['spec_planing_width']){$newproduct->setData('spec_planing_width',$data[$i]['spec_planing_width']);}
												if($data[$i]['spec_plunge_depth']){$newproduct->setData('spec_plunge_depth',$data[$i]['spec_plunge_depth']);}
												if($data[$i]['spec_range']){$newproduct->setData('spec_range',$data[$i]['spec_range']);}
												if($data[$i]['spec_shipping_weight']){$newproduct->setData('spec_shipping_weight',$data[$i]['spec_shipping_weight']);}
												if($data[$i]['spec_soft_start']){$newproduct->setData('spec_soft_start',$data[$i]['spec_soft_start']);}
												if($data[$i]['spec_speed_settings']){$newproduct->setData('spec_speed_settings',$data[$i]['spec_speed_settings']);}
												if($data[$i]['spec_spindle_lock']){$newproduct->setData('spec_spindle_lock',$data[$i]['spec_spindle_lock']);}
												if($data[$i]['spec_spindle_thread']){$newproduct->setData('spec_spindle_thread',$data[$i]['spec_spindle_thread']);}
												if($data[$i]['spec_stainles_stel_cut_capct']){$newproduct->setData('spec_stainles_stel_cut_capct',$data[$i]['spec_stainles_stel_cut_capct']);}
												if($data[$i]['spec_steel_augr_bit_capct']){$newproduct->setData('spec_steel_augr_bit_capct',$data[$i]['spec_steel_augr_bit_capct']);}
												if($data[$i]['spec_steel_drill_capct']){$newproduct->setData('spec_steel_drill_capct',$data[$i]['spec_steel_drill_capct']);}
												if($data[$i]['spec_steel_twst_bit_capct']){$newproduct->setData('spec_steel_twst_bit_capct',$data[$i]['spec_steel_twst_bit_capct']);}
												if($data[$i]['spec_stroke_length']){$newproduct->setData('spec_stroke_length',$data[$i]['spec_stroke_length']);}
												if($data[$i]['spec_suction_pressure']){$newproduct->setData('spec_suction_pressure',$data[$i]['spec_suction_pressure']);}
												if($data[$i]['spec_switch']){$newproduct->setData('spec_switch',$data[$i]['spec_switch']);}
												if($data[$i]['spec_table_size']){$newproduct->setData('spec_table_size',$data[$i]['spec_table_size']);}
												if($data[$i]['spec_tool_height']){$newproduct->setData('spec_tool_height',$data[$i]['spec_tool_height']);}
												if($data[$i]['spec_tool_length']){$newproduct->setData('spec_tool_length',$data[$i]['spec_tool_length']);}
												if($data[$i]['spec_tool_weight']){$newproduct->setData('spec_tool_weight',$data[$i]['spec_tool_weight']);}
												if($data[$i]['spec_torque']){$newproduct->setData('spec_torque',$data[$i]['spec_torque']);}
												if($data[$i]['spec_vibration_control']){$newproduct->setData('spec_vibration_control',$data[$i]['spec_vibration_control']);}
												if($data[$i]['spec_vibration_measurement']){$newproduct->setData('spec_vibration_measurement',$data[$i]['spec_vibration_measurement']);}
												if($data[$i]['spec_voltage']){$newproduct->setData('spec_voltage',$data[$i]['spec_voltage']);}
												if($data[$i]['spec_watts']){$newproduct->setData('spec_watts',$data[$i]['spec_watts']);}
												if($data[$i]['spec_wheel_disc_size']){$newproduct->setData('spec_wheel_disc_size',$data[$i]['spec_wheel_disc_size']);}
												if($data[$i]['spec_wire_cup_brush_size']){$newproduct->setData('spec_wire_cup_brush_size',$data[$i]['spec_wire_cup_brush_size']);}
												if($data[$i]['spec_wood_augr_bit_capct']){$newproduct->setData('spec_wood_augr_bit_capct',$data[$i]['spec_wood_augr_bit_capct']);}
												if($data[$i]['spec_wood_cutting_capct']){$newproduct->setData('spec_wood_cutting_capct',$data[$i]['spec_wood_cutting_capct']);}
												if($data[$i]['spec_wood_drill_capct']){$newproduct->setData('spec_wood_drill_capct',$data[$i]['spec_wood_drill_capct']);}
												if($data[$i]['spec_wood_hol_saw_capct']){$newproduct->setData('spec_wood_hol_saw_capct',$data[$i]['spec_wood_hol_saw_capct']);}
												if($data[$i]['spec_wood_selftbit_capct']){$newproduct->setData('spec_wood_selftbit_capct',$data[$i]['spec_wood_selftbit_capct']);}
												if($data[$i]['spec_wood_spd_bit_capct']){$newproduct->setspec_wood_spd_bit_capct($data[$i]['spec_wood_spd_bit_capct']);}	
												
												if($data[$i]['core_url_key']){$newproduct->setData(url_key,$data[$i]['core_url_key']);}
												if($data[$i]['ct_brand']){$newproduct->setData(ct_brand,$data[$i]['ct_brand']);}
												if($data[$i]['ct_mats']){$newproduct->setData(ct_mats,$data[$i]['ct_mats']);}
												if($data[$i]['ct_length_name']){$newproduct->setData(ct_length_name,$data[$i]['ct_length_name']);}
												if($data[$i]['ct_sz_name']){$newproduct->setData(ct_sz_name,$data[$i]['ct_sz_name']);}
												if($data[$i]['ct_fl_length']){$newproduct->setData(ct_fl_length,$data[$i]['ct_fl_length']);}
												if($data[$i]['ct_flute_no']){$newproduct->setData(ct_flute_no,$data[$i]['ct_flute_no']);}
												if($data[$i]['ct_sh_length']){$newproduct->setData(ct_sh_length,$data[$i]['ct_sh_length']);}
												if($data[$i]['ct_tot_length']){$newproduct->setData(ct_tot_length,$data[$i]['ct_tot_length']);}
												if($data[$i]['ct_dia_in']){$newproduct->setData(ct_flute_no,$data[$i]['ct_flute_no']);}
												if($data[$i]['ct_coat']){$newproduct->setData(ct_coat,$data[$i]['ct_coat']);}
												if($data[$i]['ct_corner_rad']){$newproduct->setData(ct_corner_rad,$data[$i]['ct_corner_rad']);}
												if($data[$i]['ct_cut_length']){$newproduct->setData(ct_cut_length,$data[$i]['ct_cut_length']);}
												if($data[$i]['ct_taper_per_side']){$newproduct->setData(ct_taper_per_side,$data[$i]['ct_taper_per_side']);}
												if($data[$i]['ct_heli_deg']){$newproduct->setData(ct_heli_deg,$data[$i]['ct_heli_deg']);}
												if($data[$i]['ct_hrc']){$newproduct->setData(ct_hrc,$data[$i]['ct_hrc']);}
												if($data[$i]['ct_sub_cat']){$newproduct->setData(ct_sub_cat,$data[$i]['ct_sub_cat']);}
												if($data[$i]['ct_mm_dia']){$newproduct->setData(ct_mm_dia,$data[$i]['ct_mm_dia']);}
												if($data[$i]['use_alum']){$newproduct->setData(use_alum,$alumOut);}
												if($data[$i]['use_csteel']){$newproduct->setData(use_csteel,$csteelOut);}
												if($data[$i]['use_asteel']){$newproduct->setData(use_asteel,$asteelOut);}
												if($data[$i]['use_hsteel']){$newproduct->setData(use_hsteel,$hsteelOut);}
												if($data[$i]['use_ssteel']){$newproduct->setData(use_ssteel,$ssteelOut);}
												if($data[$i]['use_nickel']){$newproduct->setData(use_nickel,$nickelOut);}
												if($data[$i]['use_ciron']){$newproduct->setData(use_ciron,$cironOut);}
												if($data[$i]['use_tit']){$newproduct->setData(use_tit,$titOut);}
												
                                                if($data[$i]['manufacturer']){
                                                	$m=$this->getManuList('manufacturer', $data[$i]['manufacturer']);
                                                	$newproduct->setManufacturer($m);
                                                }
                                               /* if($data[$i]['manufacturer']){
                                                	$m = $this->getAtSet('manufacturer');
                                                	foreach ($m as $manufacturer){
                                                		if($manufacturer['label']== $data[$i]['manufacturer']){
                                                			$newproduct->setManufacturer($manufacturer[value]);
                                                		}}}
                                               */
                                        
                                        // image gallery info
                                                $newproduct->setMediaGallery(array('images'=>array(),'values'=>array()));

                                                if($data[$i]['thumbnail']){
                                                	//$tmb="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['thumbnail'];
                                                	//$newproduct->addImageToMediaGallery($tmb,'thumbnail',false,false,"test");
                                                	$gal = $newproduct->getData('media_gallery');
                                                	$lImage = array_pop($gal['images']);
                                                	$lImage['label'] = $data[$i]['thumbnail_label'];
                                                	array_push($gal['images'],$lImage);
                                                	$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no sm image<br/>";
                                                }

                                                if($data[$i]['small_image']){
                                                	//$sim="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['small_image'];
                                                	//$newproduct->addImageToMediaGallery($sim,'small_image',false,false);
                                                	$gal = $newproduct->getData('media_gallery');
                                                	$lImage = array_pop($gal['images']);
                                                	$lImage['label'] = $data[$i]['small_image_label'];
                                                	array_push($gal['images'],$lImage);
                                                	$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no md image<br/>";
                                                }
                                                if($data[$i]['image']){
                                                	$img="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['image'];
	                                           		$newproduct->addImageToMediaGallery($img,array('thumbnail','small_image','image'),false,false);	
                                           			$gal = $newproduct->getData('media_gallery');
                                           			$lImage = array_pop($gal['images']);
                                           			$lImage['label'] = $data[$i]['image_label'];
                                           			array_push($gal['images'],$lImage);
                                           			$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no lg image<br/>";
                                                }

                                                //stock info
                                                $stockData = $newproduct->getStockData();
                                                $stockData['qty'] = 100;                      // x const 100
                                                $stockData['is_in_stock'] = 1;                // x const 1
                                                $stockData['manage_stock'] = 0;
                                                $stockData['use_config_manage_stock'] = 1;
                                                $newproduct->setStockData($stockData);
             echo $newproduct->getData['sku']." has been updated.<br/>";
                                                $newproduct->save();   
    //TODO: add Product update feature.
                                        }else{
										echo "making new product";
                                            try {
                                            	//TODO: Require SKU for all processing
												if($data[$i]['sku']){$newproduct->setData('sku',$data[$i]['sku']);}
												if($data[$i]['_store']){$newproduct->setStore($data[$i]['_store']);}
												//if($attributeSetId){$newproduct->setAttributeSetId($attributeSetId);}
												$newproduct->setAttributeSetId('73');
												if($data[$i]['_type']){$newproduct->setTypeId($data[$i]['_type']);}
												if($data[$i]['_category']){$newproduct->setCategoryIds($data[$i]['_category']);}
												//if($data[$i]['_product_websites']){$newproduct->setWebSites($data[$i]['_product_websites']);} //check<--
												$newproduct->setWebsiteIDs(array(1));
												if($data[$i]['status']){$newproduct->setStatus($data[$i]['status']);}
												if($data[$i]['tax_class_id']){$newproduct->setTaxClassId($data[$i]['tax_class_id']);}
												if($data[$i]['visibility']){$newproduct->setvisibility($data[$i]['visibility']);}
												if($data[$i]['price']){$newproduct->setPrice($data[$i]['price']);}
												if($data[$i]['enable_googlecheckout']){$newproduct->setEnable_googlecheckout($data[$i]['enable_googlecheckout']);}
												if($data[$i]['meta_title']){$newproduct->setMeta_title($data[$i]['meta_title']);}
                                                if($data[$i]['meta_description']){$newproduct->setMeta_description($data[$i]['meta_description']);}

                                                if($data[$i]['name']){$newproduct->setName($data[$i]['name']);}
												if($data[$i]['description']){$newproduct->setData('description',$data[$i]['description']);}
                                                if($data[$i]['short_description']){$newproduct->setShortDescription($data[$i]['short_description']);}
												if($data[$i]['included']){$newproduct->setIncluded($data[$i]['included']);}
                                                if($data[$i]['in_depth']){$newproduct->setIn_depth($data[$i]['in_depth']);}
                                                if($data[$i]['name']){$newproduct->setData('name',$data[$i]['name']);}
												if($data[$i]['UPC']){$newproduct->setData('upc',$data[$i]['UPC']);}
												
												if($data[$i]['spec_accuracy']){$newproduct->setData('spec_accuracy',$data[$i]['spec_accuracy']);}
												if($data[$i]['spec_air_temp']){$newproduct->setData('spec_air_temp',$data[$i]['spec_air_temp']);}
												if($data[$i]['spec_air_volume']){$newproduct->setData('spec_air_volume',$data[$i]['spec_air_volume']);}
												if($data[$i]['spec_alumn_cutting_capct']){$newproduct->setData('spec_alumn_cutting_capct',$data[$i]['spec_alumn_cutting_capct']);}
												if($data[$i]['spec_amps']){$newproduct->setData('spec_amps',$data[$i]['spec_amps']);}
												if($data[$i]['spec_arbor_size']){$newproduct->setData('spec_arbor_size',$data[$i]['spec_arbor_size']);}
												if($data[$i]['spec_base_diameter']){$newproduct->setData('spec_base_diameter',$data[$i]['spec_base_diameter']);}
												if($data[$i]['spec_base_opening']){$newproduct->setData('spec_base_opening',$data[$i]['spec_base_opening']);}
												if($data[$i]['spec_battery']){$newproduct->setData('spec_battery',$data[$i]['spec_battery']);}
												if($data[$i]['spec_bevel_miter_capct']){$newproduct->setData('spec_bevel_miter_capct',$data[$i]['spec_bevel_miter_capct']);}
												if($data[$i]['spec_bevel_capacity']){$newproduct->setData('spec_bevel_capacity',$data[$i]['spec_bevel_capacity']);}
												if($data[$i]['spec_blade_side']){$newproduct->setData('spec_blade_side',$data[$i]['spec_blade_side']);}
												if($data[$i]['spec_blade_size']){$newproduct->setData('spec_blade_size',$data[$i]['spec_blade_size']);}
												if($data[$i]['spec_blows_min_bpm']){$newproduct->setData('spec_blows_min_bpm',$data[$i]['spec_blows_min_bpm']);}
												if($data[$i]['spec_capacity']){$newproduct->setData('spec_capacity',$data[$i]['spec_capacity']);}
												if($data[$i]['spec_capacity_45dgres']){$newproduct->setData('spec_capacity_45dgres',$data[$i]['spec_capacity_45dgres']);}
												if($data[$i]['spec_capacity_50dgres']){$newproduct->setData('spec_capacity_50dgres',$data[$i]['spec_capacity_50dgres']);}
												if($data[$i]['spec_capacity_90dgres']){$newproduct->setData('spec_capacity_90dgres',$data[$i]['spec_capacity_90dgres']);}
												if($data[$i]['spec_charge_time']){$newproduct->setData('spec_charge_time',$data[$i]['spec_charge_time']);}
												if($data[$i]['spec_chipping']){$newproduct->setData('spec_chipping',$data[$i]['spec_chipping']);}
												if($data[$i]['spec_chuck_size']){$newproduct->setData('spec_chuck_size',$data[$i]['spec_chuck_size']);}
												if($data[$i]['spec_chuck_type']){$newproduct->setData('spec_chuck_type',$data[$i]['spec_chuck_type']);}
												if($data[$i]['spec_clutch']){$newproduct->setData('spec_clutch',$data[$i]['spec_clutch']);}
												if($data[$i]['spec_clutch_settings']){$newproduct->setData('spec_clutch_settings',$data[$i]['spec_clutch_settings']);}
												if($data[$i]['spec_collet_capacity']){$newproduct->setData('spec_collet_capacity',$data[$i]['spec_collet_capacity']);}
												if($data[$i]['spec_compatible_with']){$newproduct->setData('spec_compatible_with',$data[$i]['spec_compatible_with']);}
												if($data[$i]['spec_concrete_drill_capacity']){$newproduct->setData('spec_concrete_drill_capacity',$data[$i]['spec_concrete_drill_capacity']);}
												if($data[$i]['spec_cord_length']){$newproduct->setData('spec_cord_length',$data[$i]['spec_cord_length']);}
												if($data[$i]['spec_cord_type']){$newproduct->setData('spec_cord_type',$data[$i]['spec_cord_type']);}
												if($data[$i]['spec_cutting_capct']){$newproduct->setData('spec_cutting_capct',$data[$i]['spec_cutting_capct']);}
												if($data[$i]['spec_depth_adj_range']){$newproduct->setData('spec_depth_adj_range',$data[$i]['spec_depth_adj_range']);}
												if($data[$i]['spec_diameter']){$newproduct->setData('spec_diameter',$data[$i]['spec_diameter']);}
												if($data[$i]['spec_dimensions']){$newproduct->setData('spec_dimensions',$data[$i]['spec_dimensions']);}
												if($data[$i]['spec_drive_type']){$newproduct->setData('spec_drive_type',$data[$i]['spec_drive_type']);}
												if($data[$i]['spec_drive_size']){$newproduct->setData('spec_drive_size',$data[$i]['spec_drive_size']);}
												if($data[$i]['spec_electric_brake']){$newproduct->setData('spec_electric_brake',$data[$i]['spec_electric_brake']);}
												if($data[$i]['spec_impact_energy']){$newproduct->setData('spec_impact_energy',$data[$i]['spec_impact_energy']);}
												if($data[$i]['spec_laser_diode']){$newproduct->setData('spec_laser_diode',$data[$i]['spec_laser_diode']);}
												if($data[$i]['spec_led_light']){$newproduct->setData('spec_led_light',$data[$i]['spec_led_light']);}
												if($data[$i]['spec_levlg_type_range']){$newproduct->setData('spec_levlg_type_range',$data[$i]['spec_levlg_type_range']);}
												if($data[$i]['spec_masonry_drill_capcty']){$newproduct->setData('spec_masonry_drill_capcty',$data[$i]['spec_masonry_drill_capcty']);}
												if($data[$i]['spec_material']){$newproduct->setData('spec_material',$data[$i]['spec_material']);}
												if($data[$i]['spec_metal_cutting_capct']){$newproduct->setData('spec_metal_cutting_capct',$data[$i]['spec_metal_cutting_capct']);}
												if($data[$i]['spec_mild_steel_cut_capct']){$newproduct->setData('spec_mild_steel_cut_capct',$data[$i]['spec_mild_steel_cut_capct']);}
												if($data[$i]['spec_miter_detents']){$newproduct->setData('spec_miter_detents',$data[$i]['spec_miter_detents']);}
												if($data[$i]['spec_model']){$newproduct->setData('spec_model',$data[$i]['spec_model']);}
												if($data[$i]['spec_motor_hp']){$newproduct->setData('spec_motor_hp',$data[$i]['spec_motor_hp']);}
												if($data[$i]['spec_mount_threadg']){$newproduct->setData('spec_mount_threadg',$data[$i]['spec_mount_threadg']);}
												if($data[$i]['spec_noise_level']){$newproduct->setData('spec_noise_level',$data[$i]['spec_noise_level']);}
												if($data[$i]['spec_no_load_speed']){$newproduct->setData('spec_no_load_speed',$data[$i]['spec_no_load_speed']);}
												if($data[$i]['spec_operation_temptr']){$newproduct->setData('spec_operation_temptr',$data[$i]['spec_operation_temptr']);}
												if($data[$i]['spec_orbit_diameter']){$newproduct->setData('spec_orbit_diameter',$data[$i]['spec_orbit_diameter']);}
												if($data[$i]['spec_oscillating_angle']){$newproduct->setData('spec_oscillating_angle',$data[$i]['spec_oscillating_angle']);}
												if($data[$i]['spec_pad_size']){$newproduct->setData('spec_pad_size',$data[$i]['spec_pad_size']);}
												if($data[$i]['spec_paper_size_type']){$newproduct->setData('spec_paper_size_type',$data[$i]['spec_paper_size_type']);}
												if($data[$i]['spec_planing_depth']){$newproduct->setData('spec_planing_depth',$data[$i]['spec_planing_depth']);}
												if($data[$i]['spec_planing_width']){$newproduct->setData('spec_planing_width',$data[$i]['spec_planing_width']);}
												if($data[$i]['spec_plunge_depth']){$newproduct->setData('spec_plunge_depth',$data[$i]['spec_plunge_depth']);}
												if($data[$i]['spec_range']){$newproduct->setData('spec_range',$data[$i]['spec_range']);}
												if($data[$i]['spec_shipping_weight']){$newproduct->setData('spec_shipping_weight',$data[$i]['spec_shipping_weight']);}
												if($data[$i]['spec_soft_start']){$newproduct->setData('spec_soft_start',$data[$i]['spec_soft_start']);}
												if($data[$i]['spec_speed_settings']){$newproduct->setData('spec_speed_settings',$data[$i]['spec_speed_settings']);}
												if($data[$i]['spec_spindle_lock']){$newproduct->setData('spec_spindle_lock',$data[$i]['spec_spindle_lock']);}
												if($data[$i]['spec_spindle_thread']){$newproduct->setData('spec_spindle_thread',$data[$i]['spec_spindle_thread']);}
												if($data[$i]['spec_stainles_stel_cut_capct']){$newproduct->setData('spec_stainles_stel_cut_capct',$data[$i]['spec_stainles_stel_cut_capct']);}
												if($data[$i]['spec_steel_augr_bit_capct']){$newproduct->setData('spec_steel_augr_bit_capct',$data[$i]['spec_steel_augr_bit_capct']);}
												if($data[$i]['spec_steel_drill_capct']){$newproduct->setData('spec_steel_drill_capct',$data[$i]['spec_steel_drill_capct']);}
												if($data[$i]['spec_steel_twst_bit_capct']){$newproduct->setData('spec_steel_twst_bit_capct',$data[$i]['spec_steel_twst_bit_capct']);}
												if($data[$i]['spec_stroke_length']){$newproduct->setData('spec_stroke_length',$data[$i]['spec_stroke_length']);}
												if($data[$i]['spec_suction_pressure']){$newproduct->setData('spec_suction_pressure',$data[$i]['spec_suction_pressure']);}
												if($data[$i]['spec_switch']){$newproduct->setData('spec_switch',$data[$i]['spec_switch']);}
												if($data[$i]['spec_table_size']){$newproduct->setData('spec_table_size',$data[$i]['spec_table_size']);}
												if($data[$i]['spec_tool_height']){$newproduct->setData('spec_tool_height',$data[$i]['spec_tool_height']);}
												if($data[$i]['spec_tool_length']){$newproduct->setData('spec_tool_length',$data[$i]['spec_tool_length']);}
												if($data[$i]['spec_tool_weight']){$newproduct->setData('spec_tool_weight',$data[$i]['spec_tool_weight']);}
												if($data[$i]['spec_torque']){$newproduct->setData('spec_torque',$data[$i]['spec_torque']);}
												if($data[$i]['spec_vibration_control']){$newproduct->setData('spec_vibration_control',$data[$i]['spec_vibration_control']);}
												if($data[$i]['spec_vibration_measurement']){$newproduct->setData('spec_vibration_measurement',$data[$i]['spec_vibration_measurement']);}
												if($data[$i]['spec_voltage']){$newproduct->setData('spec_voltage',$data[$i]['spec_voltage']);}
												if($data[$i]['spec_watts']){$newproduct->setData('spec_watts',$data[$i]['spec_watts']);}
												if($data[$i]['spec_wheel_disc_size']){$newproduct->setData('spec_wheel_disc_size',$data[$i]['spec_wheel_disc_size']);}
												if($data[$i]['spec_wire_cup_brush_size']){$newproduct->setData('spec_wire_cup_brush_size',$data[$i]['spec_wire_cup_brush_size']);}
												if($data[$i]['spec_wood_augr_bit_capct']){$newproduct->setData('spec_wood_augr_bit_capct',$data[$i]['spec_wood_augr_bit_capct']);}
												if($data[$i]['spec_wood_cutting_capct']){$newproduct->setData('spec_wood_cutting_capct',$data[$i]['spec_wood_cutting_capct']);}
												if($data[$i]['spec_wood_drill_capct']){$newproduct->setData('spec_wood_drill_capct',$data[$i]['spec_wood_drill_capct']);}
												if($data[$i]['spec_wood_hol_saw_capct']){$newproduct->setData('spec_wood_hol_saw_capct',$data[$i]['spec_wood_hol_saw_capct']);}
												if($data[$i]['spec_wood_selftbit_capct']){$newproduct->setData('spec_wood_selftbit_capct',$data[$i]['spec_wood_selftbit_capct']);}
												if($data[$i]['spec_wood_spd_bit_capct']){$newproduct->setspec_wood_spd_bit_capct($data[$i]['spec_wood_spd_bit_capct']);}	
												
                                                if($data[$i]['manufacturer']){
                                                	$m = $this->getAtSet('manufacturer');
                                                	foreach ($m as $manufacturer){
                                                		if($manufacturer['label']== $data[$i]['manufacturer']){
                                                			$newproduct->setManufacturer($manufacturer[value]);
                                                		}}}

                                            // image gallery info
                                                $newproduct->setMediaGallery(array('images'=>array(),'values'=>array()));

                                                if($data[$i]['thumbnail']){
                                                	//$tmb="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['thumbnail'];
                                                	//$newproduct->addImageToMediaGallery($tmb,'thumbnail',false,false,"test");
                                                	$gal = $newproduct->getData('media_gallery');
                                                	$lImage = array_pop($gal['images']);
                                                	$lImage['label'] = $data[$i]['thumbnail_label'];
                                                	array_push($gal['images'],$lImage);
                                                	$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no sm image<br/>";
                                                }

                                                if($data[$i]['small_image']){
                                                	//$sim="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['small_image'];
                                                	//$newproduct->addImageToMediaGallery($sim,'small_image',false,false);
                                                	$gal = $newproduct->getData('media_gallery');
                                                	$lImage = array_pop($gal['images']);
                                                	$lImage['label'] = $data[$i]['small_image_label'];
                                                	array_push($gal['images'],$lImage);
                                                	$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no md image<br/>";
                                                }
                                                if($data[$i]['image']){
                                                	$img="C:\\xampp\\htdocs\\magento\\media\\catalog\\test\\".$data[$i]['image'];
	                                           		$newproduct->addImageToMediaGallery($img,array('thumbnail','small_image','image'),false,false);	
                                           			$gal = $newproduct->getData('media_gallery');
                                           			$lImage = array_pop($gal['images']);
                                           			$lImage['label'] = $data[$i]['image_label'];
                                           			array_push($gal['images'],$lImage);
                                           			$newproduct->setData('media_gallery',$gal);
                                                }else{
                                                //	echo "no lg image<br/>";
                                                }

                                                //stock info
                                                $stockData = $newproduct->getStockData();
                                                $stockData['qty'] = 100;                      // x const 100
                                                $stockData['is_in_stock'] = 1;                // x const 1
                                                $stockData['manage_stock'] = 0;
                                                $stockData['use_config_manage_stock'] = 1;
                                                $newproduct->setStockData($stockData);

                                                //assign product to the default website
                                                //$newproduct->setWebsiteIds(array(Mage::app()->getStore(true)->getWebsite()->getId()));
                                                $newproduct->save();
echo $newproduct->getData['sku']." has been added.<br/>";
                                            }
                                            catch (Mage_Core_Exception $e) {
                                                echo "Save error".$e->getMessage();
                                            }											
                                        }  
                                    }    

                                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__(count($data[$i]).' The new products have been created.'));                                    //$this->_redirect('*/*/');
                                }else{
                                    Mage::getSingleton('adminhtml/session')->addError('You cannot process a disabled file');
 //                                   $this->_redirect('*/*/');
                                }
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
//				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
                print_r(var_dump($this->getRequest()->getParam('produp1')));
//		$this->_redirect('*/*/');
	}        

	/*
	 * takes arg_attribute (i.e. manufacturer) and arg_value (i.e. Bosch) and returns the attribute option code for an existing attribute option or the new code for
	 * an attribute option it creates.
	 */
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

		return false;
	}
	/*
	 * tests if a value option exitst works with getManuList function. Or, on it's own I guess. yay.
	 */
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
			if ($option['label'] == $arg_value || $option['label'] == strtolower($arg_value) || $option['label'] == strtoupper($arg_value))
			{
				return $option['value'];
			}
		}
	
		return false;
	}
	
    public function massDeleteAction() {
        $produp1Ids = $this->getRequest()->getParam('produp1');
        if(!is_array($produp1Ids)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($produp1Ids as $produp1Id) {
                    $produp1 = Mage::getModel('produp1/produp1')->load($produp1Id);
                    $produp1->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($produp1Ids)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
    public function massStatusAction()
    {
        $produp1Ids = $this->getRequest()->getParam('produp1');
        if(!is_array($produp1Ids)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($produp1Ids as $produp1Id) {
                    $produp1 = Mage::getSingleton('produp1/produp1')
                        ->load($produp1Id)
                        ->setStatus($this->getRequest()->getParam('status'))
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) were successfully updated', count($produp1Ids))
                );
            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'produp1.csv';
        $content    = $this->getLayout()->createBlock('produp1/adminhtml_produp1_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'produp1.xml';
        $content    = $this->getLayout()->createBlock('produp1/adminhtml_produp1_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}
