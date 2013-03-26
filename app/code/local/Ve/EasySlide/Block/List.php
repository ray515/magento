<?php
/*------------------------------------------------------------------------
# Ve EasySlide - Banner Slide
# ------------------------------------------------------------------------
# author    Vsmart Extensions
# copyright Copyright (C) 2010 Vsmart-extensions.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://www.vsmart-extensions.com
# Technical Support:  Forum - http://www.vsmart-extensions.com
-------------------------------------------------------------------------*/
class Ve_EasySlide_Block_List extends Mage_Catalog_Block_Product_List 
{
	var $_config = array();
	
	public function __construct($attributes = array()){
	
		$helper =  Mage::helper('ve_easyslide/data');		
		$this->_config['show'] = $helper->get('show', $attributes);
		if(!$this->_config['show']) return;			
		parent::__construct();		
			
		$this->_config['title'] = $helper->get('title', $attributes);
		$this->_config['folder'] = $helper->get('folder', $attributes);		
		$this->_config['height'] = $helper->get('height', $attributes);
		$this->_config['width'] = $helper->get('width', $attributes);
		$this->_config['autoplay'] = $helper->get('autoplay', $attributes);
		$this->_config['controlnumber'] = $helper->get('controlnumber', $attributes);
		$this->_config['npbutton'] = $helper->get('npbutton', $attributes);
		$this->_config['duration'] = $helper->get('duration', $attributes);
		$this->_config['interval'] = $helper->get('interval', $attributes);
		$this->_config['thumbnailMode'] = $helper->get('thumbnailMode', $attributes);
		for($i=1; $i <= 10; $i++) 
		{
			$image = 'image'.$i;			
			$link = 'link'.$i;
			$imagename = $helper->get($image, $attributes);
			if($this->_config['thumbnailMode'] != "none"){
				$strreplace = '_'.$this->_config['width'].'_'.$this->_config['height'].'.';			
				$imagename = @str_replace('.',$strreplace,$imagename);
			}
			$this->_config[$image] = $imagename;
			$this->_config[$link] = $helper->get($link, $attributes);
		}	
		
		$this->_config['template'] = 've/easyslide/list.phtml';	
		
	}		
	function _toHtml() 
	{			
		if(!$this->_config['show']) return;	
		$this->__renderSlideShowImages();
		$this->assign('configs', $this->_config);			
		$this->setTemplate($this->_config['template']);	
        return parent::_toHtml();	
	}
	
	private function __renderSlideShowImages(){
		// init values.
		$mainsThumbs = $imageArray     = array();		
		$listImgs = $this->getFileInDir();	
		if (count($listImgs) > 0) 
		{			
			foreach($listImgs as $k=>$img) 
			{
				$imageArray[] = $this->_config['folder'].'/'.$img;
			}			
			$mainsThumbs = $this->buildThumbnail( $imageArray, $this->_config['width'], $this->_config['height'] );
		}
		
		$this->assign('mainsThumbs', $mainsThumbs);		
		$this->assign('listImgs', $imageArray);				
	}
	
	//Get File in Dir
	function getFileInDir()
	{
		if(!$this->_config['folder']) return ;
		
		$imagePath = Mage::getBaseDir().DIRECTORY_SEPARATOR.$this->_config['folder'];


		if(!is_dir($imagePath)) return array();
		$imgFiles   = $this->files($imagePath);
				
		$folderPath = $imagePath .DIRECTORY_SEPARATOR;
		$list = array();

		foreach ($imgFiles as $file)
		{
			$i_f 	= $imagePath .'/'. $file;
			if ( eregi( "bmp|gif|jpg|png|jpeg", $file ) && is_file( $i_f ) ) {
				$list[] = $file;			
			}
		}
		return $list;
	}
	//
	function files($path, $filter = '.', $recurse = false, $fullpath = false, $exclude = array('.svn', 'CVS'))
	{
		// Initialize variables
		$arr = array ();
		// Is the path a folder?
		if (!is_dir($path)) {
			return array();
		}
		// read the source directory
		$handle = opendir($path);
		while (($file = readdir($handle)) !== false)
		{
			$dir = $path.DIRECTORY_SEPARATOR.$file;
			$isDir = is_dir($dir);
			if (($file != '.') && ($file != '..') && (!in_array($file, $exclude))) {
				if ($isDir) {
					if ($recurse) {
						if (is_integer($recurse)) {
							$recurse--;
						}
						$arr2 = files($dir, $filter, $recurse, $fullpath);
						$arr = array_merge($arr, $arr2);
					}
				} else {
					if (preg_match("/$filter/", $file)) {
						if ($fullpath) {
							$arr[] = $path.'/'.$file;
						} else {
							$arr[] = $file;
						}
					}
				}
			}
		}
		closedir($handle);
		asort($arr);
		return $arr;
	}
	
	function buildThumbnail ( $imageArray, $twidth, $theight ) 
	{	
		$thumbnailMode = $this->_config['thumbnailMode'];		
		if( $thumbnailMode != 'none' )
		{	
			$thumbs = array();
			$veimage =  Mage::helper('ve_easyslide/veimage');		
			$crop = $thumbnailMode == 'crop' ? true:false;		
			foreach ($imageArray as $image) 
			{
				$thumbs[]  = $veimage->resize( $image,$twidth, $theight, $crop, 1 );
			}
		} else 
		{
			return $imageArray;
		}
		return $thumbs;
	}
}