<?php

class TRU_ProdUp1_Block_Adminhtml_ProdUp1_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('produp1Grid');
      $this->setDefaultSort('produp1_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
      
  }
  
  protected function _prepareCollection()
  {
      $collection = Mage::getModel('produp1/produp1')->getCollection();
      $this->setCollection($collection);
      return parent::_prepareCollection();

  }

  protected function _prepareColumns()
  {
  	$this->addColumn('produp1_id', array(
          'header'    => Mage::helper('produp1')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
  			'type'	  => 'text',
          'index'     => 'produp1_id',
      ));

      $this->addColumn('title', array(
          'header'    => Mage::helper('produp1')->__('Title'),
          'align'     =>'left',
          'width'     =>'150',
          'index'     => 'title'//,
      ));

      $this->addColumn('content', array(
			'header'    => Mage::helper('produp1')->__('File Notes'),
			//'width'     => '150px',
			'index'     => 'content'//,
      ));

      $this->addColumn('status', array(
          'header'    => Mage::helper('produp1')->__('Status'),
          'align'     => 'left',
          'width'     => '80px',
          'index'     => 'status',
          'type'      => 'options',
          'options'   => array(
              1 => 'Enabled',
              2 => 'Disabled',
          ),
      ));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('produp1')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('produp1')->__('Edit'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('produp1')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('produp1')->__('XML'));
	  //echo('tester2');
      return $this;
  		//return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('produp1_id');
        $this->getMassactionBlock()->setFormFieldName('produp1');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('produp1')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('produp1')->__('Are you sure?')
        ));

         $this->getMassactionBlock()->addItem('process', array(
             'label'    => Mage::helper('produp1')->__('Process'),
             'url'      => $this->getUrl('*/*/process'),
             'confirm'  => Mage::helper('produp1')->__('Are you sure?')
        ));       
        
        $statuses = Mage::getSingleton('produp1/status')->getOptionArray();

        array_unshift($statuses, array('label'=>'', 'value'=>''));
        $this->getMassactionBlock()->addItem('status', array(
             'label'=> Mage::helper('produp1')->__('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', array('_current'=>true)),
             'additional' => array(
                    'visibility' => array(
                         'name' => 'status',
                         'type' => 'select',
                         'class' => 'required-entry',
                         'label' => Mage::helper('produp1')->__('Status'),
                         'values' => $statuses
                     )
             )
        ));
        
        return $this;
    }

  public function getRowUrl($row)
  {
      return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }
  public function getGridUrl(){
  	return $this->getUrl('*/*/grid', array('_current'=>true));
  }

}
