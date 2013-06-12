<?php
class TRU_ProdUp1_Block_ProdUp1 extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getProdUp1()     
     { 
        if (!$this->hasData('produp1')) {
            $this->setData('produp1', Mage::registry('produp1'));
        }
        return $this->getData('produp1');
        
    }
}