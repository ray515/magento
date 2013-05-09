<?php
class Mage_Catalog_Block_Product_View_Chart extends Mage_Catalog_Block_Product_View_Abstract
{
	/**
	 * Flag, that defines whether gallery is disabled
	 *
	 * @var boolean
	 */
	//protected $_isGalleryDisabled;

	/**
	 * Retrieve list of gallery images
	 *
	 * @return array|Varien_Data_Collection
	 
	public function getGalleryImages()
	{
		if ($this->_isGalleryDisabled) {
			return array();
		}
		$collection = $this->getProduct()->getMediaGalleryImages();
		return $collection;
	}
*/
	/**
	 * Retrieve gallery url
	 *
	 * @param null|Varien_Object $image
	 * @return string
	 
	public function getGalleryUrl($image = null)
	{
		$params = array('id' => $this->getProduct()->getId());
		if ($image) {
			$params['image'] = $image->getValueId();
		}
		return $this->getUrl('catalog/product/gallery', $params);
	}
*/
	/**
	 * Disable gallery
	 
	public function disableGallery()
	{
		$this->_isGalleryDisabled = true;
	}
*/
}
