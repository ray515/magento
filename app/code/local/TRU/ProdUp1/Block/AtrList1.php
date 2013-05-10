<?php
class TRU_ProdUp1_Block_Adminhtml_AtrList1 extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_produp1';
		$this->_blockGroup = 'produp1';
		$this->_headerText = Mage::helper('produp1')->__('TRU Product Uploader');
		$this->_addButtonLabel = Mage::helper('produp1')->__('Upload CSV');
		parent::__construct();
	}
}