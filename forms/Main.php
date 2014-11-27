<?php 
/**
 * BulkMetadataEditor main admin form
 *
 * This Omeka curator form collects information defining a set of 
 * bulk edits to perform on the omeka database. It includes 
 * functionality to perform the changes, and to return preview 
 * of the affected records.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * BulkMetadataEditor main admin form class
 *
 * This Omeka curator form collects information defining a set of 
 * bulk edits to perform on the omeka database. It includes 
 * functionality to perform the changes, and to return preview 
 * of the affected records.
 *
 */

class BulkMetadataEditor_Form_Main extends Omeka_Form
{
   /**
     * Initialize the form.
     *
     *@return void
     */
    public function init()
    {
      //require_once(dirname(dirname(__FILE__)).'/views/scripts/FormRadio.php');

      parent::init();

      $this->setAttrib('id', 'bulk-metadata-editor-form');
      $this->setMethod('post');

      $this->_registerElements();
	
    }

   /**
     * Populate the form
     *
     *@return void
     */
    private function _registerElements()
    {
      $this->addElement('hidden',"callback",array("value"=>""));

      $this->addElement('select','bmeCollectionId',array(
            'label'         => __('Collection'),
            'description'   => __('Edit items from this collection'),
            'value'         => '0',
	    'order'         => 1,
            'required'      => true,
	    'multiOptions'       => $this->_getCollectionOptions()
							      )
		       );


      $this->addElement('checkbox', 'itemSelectMeta', array(
            'label'         => __('Select Items by Metadata'),
	    'id' => 'item-select-meta',
            'description'   => __('Select items to edit based on their associated metadata elements'),
	    'order'         => 2
							      )
			  );

      //not actually a text element, but 
      //rendered with its own viewscript so it doesn't matter
      $this->addElement('text', 'rulebox', array(
          'order'=>3,
          'decorators' => array(
              array(
                  'ViewScript', 
                  array(
                      'viewScript' => 'FormRulebox.php',
                      'class'      => 'field'
                  )
              )
          )
      )
      );
      


      $this->addElement('button', 'previewItemsButton', array(
	    'label'=>'Preview Selected Items',
	    'class' => 'preview-button',
	    'id' => 'preview-items-button',
	    'order'         => 4
							      )
			);

      $this->addElement('button', 'hideItemPreview', array(
	    'label'=>'Hide Item Preview',
	    'id' => 'hide-item-preview',
	    'class' => 'hide-preview',
	    'order'         => 5
							      )
			);

      //not actually a text element, but 
      //rendered with its own viewscript so it doesn't matter
      $this->addElement('text', 'itemPreviewDiv', array(
						 'order'=>6,
						 'decorators' => array(array(
					      'ViewScript', 
					      array(
						    'viewScript' => 'FormPreviewDiv.php',
						    'class'      => 'field'
						    )
									     ))
					)
			  );

      $this->addElement('select', 'selectFields[]', array(
							'label'         => __('Metadata elements'),
							'description'   => __('Select the metadata elements you would like to edit. You can select multiple values. (default: all)'),
							'size'=>10,
							'order'         => 7,
							'multiple' =>  'multiple',
							'multiOptions'       => $this->_getElementOptions()
							)
			);
      
      $this->addElement('button', 'previewFieldsButton', array(
	    'label'=>'Preview Selected Fields',
	    'class' => 'preview-button',
	    'id' => 'preview-fields-button',
	    'order'         => 8
							      )
			);

      $this->addElement('button', 'hideFieldPreview', array(
	    'label'=>'Hide Field Preview',
	    'class' => 'hide-preview',
	    'id' => 'hide-field-preview',
	    'order'         => 9
							      )
			);

      //not actually a text element, but 
      //rendered with its own viewscript so it doesn't matter
      $this->addElement('text', 'fieldPreviewDiv', array(
						 'order'=>10,
						 'decorators' => array(array(
					      'ViewScript', 
					      array(
						    'viewScript' => 'FormPreviewDiv.php',
						    'class'      => 'field'
						    )
									     ))
					)
			);

 $this->addElement('radio', 'changesRadio', array(
            'label'         => __('Edit Type'),
            'description'   => __('Choose the type of edit you would like to perform'),
	    'order'         => 11,
	    'decorators'    => array(
					array(
					      'ViewScript', 
					      array(
						    'viewScript' => 'FormRadio.php',
						    'class'      => 'field'
						    )
					      )
				     ),
	    'multiOptions'       => array(
					  'replace'=>'Search and replace text',
					  'add'=>'Add a new metadatum in the selected field',
					  'append'=>'Append text to existing metadata in the selected fields',
					  'delete'=>'Delete all existing metadata in the selected fields'	  
					  )
							   )
			  );

 
      $this->addElement('button', 'previewChangesButton', array(
	    'label'=>'Preview Changes',
	    'id' => 'preview-changes-button',
	    'class' => 'preview-button',
	    'order'         => 12
							      )
			);

      $this->addElement('button', 'hideChangesPreview', array(
	    'label'=>'Hide Change Preview',
	    'class' => 'hide-preview',
	    'id' => 'hide-changes-preview',
	    'order'         => 13
							      )
			);

      //not actually a text element, but 
      //rendered with its own viewscript so it doesn't matter
      $this->addElement('text', 'changesPreviewDiv', array(
						 'order'=>14,
						 'decorators' => array(
					array(
					      'ViewScript', 
					      array(
						    'viewScript' => 'FormPreviewDiv.php',
						    'class'      => 'field'
						    )
					      )
					)
						 )
			);

      //The following elements will be re-ordered in javascript
      //gotta create a new element that can be hidden and shown and junk?

      $this->addElement('text','bmeSearch', array(
	        'label'=>'Search for:',
		'id'=>'bulk-metadata-editor-search',
		'class'=>'elementHidden',
		'description'=>'Input text you want to search for '
						       )
			);
      $this->addElement('text','bmeReplace', array(
	        'label'=>'Replace with:',
		'id'=>'bulk-metadata-editor-replace',
		'class'=>'elementHidden',
		'description'=>'Input text you want to replace with '
						       )
			);
      $this->addElement('checkbox','regexp', array(
	        'description'=>'Use regular expressions',
		'id'=>'regexp',
		'class'=>'elementHidden',
		'value'=>'true'
						       )
			);
      $this->addElement('text','bmeAdd', array(
	        'label'=>'Text to Add',
		'id'=>'bulk-metadata-editor-add',
		'class'=>'elementHidden',
		'description'=>'Input text you want to add as metadata'
						       )
			);
      $this->addElement('text','bmeAppend', array(
	        'label'=>'Text to Append',
		'id'=>'bulk-metadata-editor-append',
		'class'=>'elementHidden',
		'description'=>'Input text you want to append to metadata'
						       )
			);



      
      $this->addDisplayGroup(array(
				   'bmeCollectionId', 
				   'itemSelectMeta',
				   'rulebox',
				   'previewItemsButton',
				   'hideItemPreview',
				   'itemPreviewDiv'
				   ), 
			     'bmeItemsSet',
			     array(
				   'legend'=>'Step 1: Select Items',
				   'class'=>'bmeFieldset'
				   ));
      
      $this->addDisplayGroup(array( 
				   'selectFields[]',
				   'previewFieldsButton',
				   'hideFieldPreview',
				   'fieldPreviewDiv'
				   ), 'bmeFieldsSet',
			     array(
				   'legend'=>'Step 2: Select Fields',
				   'class'=>'bmeFieldset'
				   ));
      
      $this->addDisplayGroup(array( 
				   'changesRadio',
				   'previewChangesButton',
				   'bmeAppend',
				   'regexp',
				   'bmeAdd',
				   'bmeSearch',
				   'bmeReplace',
				   'hideChangesPreview',
				   'changesPreviewDiv'
				    ), 'bmeChangesSet',
			     array(
				   'legend'=>'Step 3: Define Changes',
				   'description'=>'Define Edits to Apply',
				   'class'=>'bmeFieldset'
				   ));


      
      $this->addElement('submit', 'performButton', array(
	    'label'=>'Apply Edits Now',
	    'order'         => 99
							 )
			);

    }


    /**
     * Overrides standard omeka form behavior to tweak display 
     *and fix radio display eccentricity
     * 
     * @return void
     */
    public function applyOmekaStyles()
    {
      foreach ($this->getElements() as $element) {

	if ($element instanceof Zend_Form_Element_Submit) {
	  // All submit form elements should be wrapped in a div with 
	  // class "field".
	  $element->setDecorators(array(
					'ViewHelper', 
					array('HtmlTag', array('tag' => 'div'))
					)
				  );

	} else if($element->getAttrib('class')=='elementHidden') {
	  $element->getDecorator('FieldTag')->setOption('class','field bmeHidden');
	  $id = $element->getAttrib('id');
	  
	  $element->getDecorator('FieldTag')->setOption('id',$id.'-field');
							

	} else if ($element instanceof Zend_Form_Element_Hidden 
		   || $element instanceof Zend_Form_Element_Hash) {
	  $element->setDecorators(array('ViewHelper'));
	}
      }
    }


    /**
     * Get an array to be used in 'select' elements containing all collections.
     * 
     * @return array $collectionOptions Array of all collections and their
     * IDs, which will be used to populate a dropdown menu on the main view
     */
    private function _getCollectionOptions()
    {
      $collections = get_records('Collection',array(),'0');
      $options = array('0'=>'All Collections');
      foreach ($collections as $collection)
	{
	  $titles = $collection->getElementTexts('Dublin Core','Title');
	  if(isset($titles[0]))
	    $title = $titles[0];
	  $options[$collection->id]=$title;
	}

      return $options;
    }

    /**
     * Get an array to be used in html select input
 containing all elements.
     * 
     * @return array $elementOptions Array of options for a dropdown
     * menu containing all elements applicable to records of type Item
     */
    private function _getElementOptions()
    {
        $db = get_db();
        $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, 
        e.name AS element_name, it.name AS item_type_name
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
         WHERE es.record_type IS NULL OR es.record_type = 'Item' 
        ORDER BY es.name, it.name, e.name";
        $elements = $db->fetchAll($sql);
        $options = array();
	//        $options = array('' => __('Select Below'));
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] 
                      ? __('Item Type') . ': ' . __($element['item_type_name']) 
                      : __($element['element_set_name']);
            $value = __($element['element_name']);
            
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }
}