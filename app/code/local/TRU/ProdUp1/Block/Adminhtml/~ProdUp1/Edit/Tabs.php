<?php

class TRU_ProdUp1_Block_Adminhtml_ProdUp1_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{

  public function __construct()
  {
      parent::__construct();
      $this->setId('produp1_tabs');
      $this->setDestElementId('edit_form');
      $this->setTitle(Mage::helper('produp1')->__('Item Information'));
  }

  protected function _beforeToHtml()
  {
      $this->addTab('form_section', array(
          'label'     => Mage::helper('produp1')->__('Item Information'),
          'title'     => Mage::helper('produp1')->__('Item Information'),
          'content'   => $this->getLayout()->createBlock('produp1/adminhtml_produp1_edit_tab_form')->toHtml(),
      ));
     
      return parent::_beforeToHtml();
  }
}