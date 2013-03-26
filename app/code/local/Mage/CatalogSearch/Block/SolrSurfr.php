<?php
class Mage_CatalogSearch_Block_SolrSurfr extends Mage_Core_Block_Template
{
	/**
	 * Catalog Product collection
	 *
	 * @var Mage_CatalogSearch_Model_Resource_Fulltext_Collection
	 */
	//protected $_productCollection;
	
	/**
	 * Retrieve loaded category collection
	 *
	 * @return Mage_CatalogSearch_Model_Resource_Fulltext_Collection
	
	protected function _getProductCollection()
	{
		if (is_null($this->_productCollection)) {
			$this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
		}
	
		return $this->_productCollection;
	}
	*/
}