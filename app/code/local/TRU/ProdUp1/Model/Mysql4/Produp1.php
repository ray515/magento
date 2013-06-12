<?php

class TRU_ProdUp1_Model_Mysql4_ProdUp1 extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the produp1_id refers to the key field in your database table.
        $this->_init('produp1/produp1', 'produp1_id');
    }
}