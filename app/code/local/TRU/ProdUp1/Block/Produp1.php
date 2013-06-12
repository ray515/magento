<?php
class TRU_Produp1_Block_Produp1 extends Mage_Core_Block_Template
{
	public function _prepareLayout()
    {
		return parent::_prepareLayout();
    }
    
     public function getProdup1()     
     { 
        if (!$this->hasData('produp1')) {
            $this->setData('produp1', Mage::registry('produp1'));
        }
        return $this->getData('produp1');
        
    }
}
