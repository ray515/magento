<?php

class TRU_ProdUp1_Block_Adminhtml_ProdUp1_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
                 
        $this->_objectId = 'id';
        $this->_blockGroup = 'produp1';
        $this->_controller = 'adminhtml_produp1';
        
        $this->_updateButton('save', 'label', Mage::helper('produp1')->__('Save Item'));
        $this->_updateButton('delete', 'label', Mage::helper('produp1')->__('Delete Item'));
		
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('produp1_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'produp1_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'produp1_content');
                }
            }

            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if( Mage::registry('produp1_data') && Mage::registry('produp1_data')->getId() ) {
            return Mage::helper('produp1')->__("Edit Item '%s'", $this->htmlEscape(Mage::registry('produp1_data')->getTitle()));
        } else {
            return Mage::helper('produp1')->__('Add Item');
        }
    }
}