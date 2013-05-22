<?php

class TRU_ProdUp1_Model_Mysql4_ProdUp1_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('produp1/produp1');
    }
}