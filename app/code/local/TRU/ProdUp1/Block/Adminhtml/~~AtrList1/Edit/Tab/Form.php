<?php

class TRU_ProdUp1_Block_Adminhtml_AtrList1_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
  protected function _prepareForm()
  {
      $form = new Varien_Data_Form();
      $this->setForm($form);
      $fieldset = $form->addFieldset('produp1_form', array('legend'=>Mage::helper('produp1')->__('Item information')));
     
      /*
      $fieldset->addField('title', 'text', array(
          'label'     => Mage::helper('produp1')->__('Title'),
          'class'     => 'required-entry',
          'required'  => true,
          'name'      => 'title',
      ));
       */

      $fieldset->addField('filename', 'file', array(
          'label'     => Mage::helper('produp1')->__('File'),
          'required'  => false,
          'name'      => 'filename',
	  ));
		
      $fieldset->addField('status', 'select', array(
          'label'     => Mage::helper('produp1')->__('Status'),
          'name'      => 'status',
          'values'    => array(
              array(
                  'value'     => 1,
                  'label'     => Mage::helper('produp1')->__('Enabled'),
              ),

              array(
                  'value'     => 2,
                  'label'     => Mage::helper('produp1')->__('Disabled'),
              ),
          ),
      ));
     
      $fieldset->addField('content', 'editor', array(
          'name'      => 'content',
          'label'     => Mage::helper('produp1')->__('File Notes'),
          'title'     => Mage::helper('produp1')->__('Content'),
          'style'     => 'width:300px; height:200px;',
          'wysiwyg'   => false,
          'required'  => true,
      ));
     
      if ( Mage::getSingleton('adminhtml/session')->getProdUp1Data() )
      {
          $form->setValues(Mage::getSingleton('adminhtml/session')->getProdUp1Data());
          Mage::getSingleton('adminhtml/session')->setProdUp1Data(null);
      } elseif ( Mage::registry('produp1_data') ) {
          $form->setValues(Mage::registry('produp1_data')->getData());
      }
      return parent::_prepareForm();
  }
}