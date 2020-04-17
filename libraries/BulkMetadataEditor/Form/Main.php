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
 * bulk edits to perform on the Omeka database. It includes
 * functionality to perform the changes, and to return preview
 * of the affected records.
 *
 */
class BulkMetadataEditor_Form_Main extends Omeka_Form
{

	/**
	 * Initialize the form.
	 *
	 * @return void
	 */
	public function init()
	{
		parent::init();

		$this->setAttrib('id', 'bulk-metadata-editor-form');
		$this->setMethod('post');

		$this->_registerElements();
	}

	/**
	 * Populate the form
	 *
	 * @return void
	 */
	private function _registerElements()
	{
		$this->addElement('hidden', 'callback', array('value' => ''));

		$this->addElement('select', 'bmeCollectionId', array(
			'label' => __('Collection'),
			'description' => __('Edit items from this collection'),
			'value' => '0',
			'multiOptions' => $this->_getCollectionOptions(),
			'order' => 1,
		));

		$this->addElement('checkbox', 'itemSelectMeta', array(
			'label' => __('Select Items by Metadata'),
			'id' => 'item-select-meta',
			'description' => __('Select items to edit based on their associated metadata elements'),
			'order' => 2,
		));

		//not actually a text element, but
		//rendered with its own viewscript so it doesn't matter
		$this->addElement('text', 'rulebox', array(
			'order' => 3,
			'decorators' => array(
				array(
					'ViewScript',
					array(
						'viewScript' => 'form-rule-box.php',
						'class' => 'field',
					)
				)
			)
		));

		$this->addElement('button', 'previewItemsButton', array(
			'label' => __('Preview Selected Items'),
			'class' => 'preview-button',
			'id' => 'preview-items-button',
			'order' => 4,
		));

		//not actually a text element, but
		//rendered with its own viewscript so it doesn't matter
		$this->addElement('text', 'itemPreviewDiv', array(
			'order' => 6,
			'decorators' => array(
				array(
					'ViewScript',
					array(
						'viewScript' => 'form-preview-div.php',
						'class' => 'field',
					)
				)
			)
		));

		$this->addElement('select', 'selectFields[]', array(
			'label' => __('Metadata elements'),
			'description' => __('Select the metadata elements you would like to edit; you can select multiple elements'),
			'size' => 10,
			'multiple' => 'multiple',
			'multiOptions' => $this->_getElementOptions(),
			'order' => 7,
		));
		
		$this->addElement('button', 'previewFieldsButton', array(
			'label' => __('Preview Selected Fields'),
			'class' => 'preview-button',
			'id' => 'preview-fields-button',
			'order' => 8,
		));

		//not actually a text element, but
		//rendered with its own viewscript so it doesn't matter
		$this->addElement('text', 'fieldPreviewDiv', array(
			'order' => 10,
			'decorators' => array(
				array(
					'ViewScript',
					array(
						'viewScript' => 'form-preview-div.php',
						'class' => 'field',
					)
				)
			)
		));

		$this->addElement('radio', 'changesRadio', array(
			'label' => __('Edit Type'),
			'description' => __('Choose the type of edit you would like to perform'),
			'order' => 11,
			'decorators' => array(
				array(
					'ViewScript',
					array(
						'viewScript' => 'form-radio.php',
						'class' => 'field',
					)
				)
			),
			'multiOptions' => array(
				'replace' => __('Search and replace text'),
				'add' => __('Add a new metadatum in the selected field'),
				'prepend' => __('Prepend text to existing metadata in the selected fields'),
				'append' => __('Append text to existing metadata in the selected fields'),
				'trim' => __('Remove text from ends of existing metadata in the selected fields'),
				'caseconvert' => __('Convert to uppercase or lowercase existing metadata in the selected fields'),
				'explode' => __('Explode metadata with a separator in multiple elements in the selected fields'),
				'deduplicate' => __('Deduplicate and remove empty metadata in the selected fields'),
				'deduplicate-files' => __('Deduplicate files of selected items by hash'),
				'delete' => __('Delete all existing metadata in the selected fields')
			)
		));

		$this->addElement('button', 'previewChangesButton', array(
			'label' => __('Preview Changes'),
			'id' => 'preview-changes-button',
			'class' => 'preview-button',
			'order' => 12,
		));

		//not actually a text element, but
		//rendered with its own viewscript so it doesn't matter
		$this->addElement('text', 'changesPreviewDiv', array(
			'order' => 14,
			'decorators' => array(
				array(
					'ViewScript',
					array(
						'viewScript' => 'form-preview-div.php',
						'class' => 'field',
					)
				)
			)
		));

		$this->addElement('checkbox', 'useBackgroundJob', array(
			'label' => __('Background Job'),
			'id' => 'use-background-job',
			'description' => __('If checked, the job will be processed in the background'),
			'value' => '1',
			'order' => 15,
		));

		//The following elements will be re-ordered in javascript
		//gotta create a new element that can be hidden and shown and junk?

		$this->addElement('text', 'bmeSearch', array(
			'label' => __('Search for'),
			'id' => 'bulk-metadata-editor-search',
			'class' => 'elementHidden',
			'description' => __('Input text you want to search for:'),
		));
		$this->addElement('checkbox', 'bmeRegexp', array(
			'label' => __('Type of search'),
			'id' => 'bulk-metadata-editor-regexp',
			'class' => 'elementHidden',
			'value' => 'true',
			'description' => __('Use regular expressions (always enclose search pattern between delimiters; visit %s for more info)', '<a href="https://www.regular-expressions.info" target="_blank" rel="external">regular-expressions.info</a>'),
		));
		$this->addElement('text', 'bmeReplace', array(
			'label' => __('Replace with'),
			'id' => 'bulk-metadata-editor-replace',
			'class' => 'elementHidden',
			'description' => __('Input text you want to replace with:'),
		));
		$this->addElement('text', 'bmeAdd', array(
			'label' => __('Text to Add'),
			'id' => 'bulk-metadata-editor-add',
			'class' => 'elementHidden',
			'description' => __('Input text you want to add as new metadatum:'),
		));
		$this->addElement('text', 'bmePrepend', array(
			'label' => __('Text to Prepend'),
			'id' => 'bulk-metadata-editor-prepend',
			'class' => 'elementHidden',
			'description' => __('Input text you want to prepend to metadata:'),
		));
		$this->addElement('text', 'bmeAppend', array(
			'label' => __('Text to Append'),
			'id' => 'bulk-metadata-editor-append',
			'class' => 'elementHidden',
			'description' => __('Input text you want to append to metadata:'),
		));
		$this->addElement('text', 'bmeLtrim', array(
			'label' => __('Text to Remove from left'),
			'id' => 'bulk-metadata-editor-ltrim',
			'class' => 'elementHidden',
			'description' => __('Input text you want to remove from the beginning of metadata (if empty, will remove white spaces and tabs)'),
		));
		$this->addElement('text', 'bmeRtrim', array(
			'label' => __('Text to Remove from right'),
			'id' => 'bulk-metadata-editor-rtrim',
			'class' => 'elementHidden',
			'description' => __('Input text you want to remove from the end of metadata (if empty, will remove white spaces and tabs)'),
		));
		$this->addElement('select', 'bmeCaseconvert', array(
			'label' => __('Case conversion type'),
			'id' => 'bulk-metadata-editor-caseconvert',
			'class' => 'elementHidden',
			'description' => __('Choose the type of case conversion for the metadata'),
			'size' => 1,
			'multiOptions' => array(
				'lower' => __('all characters lowercase'),
				'upper' => __('ALL CHARACTERS UPPERCASE'),
				'first' => __('First character of every sentence uppercase'),
				'words' => __('First Character Of Every Word Uppercase'),
			),
		));
		$this->addElement('text', 'bmeExplode', array(
			'label' => __('Separator'),
			'id' => 'bulk-metadata-editor-explode',
			'class' => 'elementHidden',
			'description' => __('The separator used to explode metadata (usually \',\' or \';\' or \'|\' or any chain of characters);')
				. ' ' . __('HTML tags will be stripped before process'),
		));

		$this->addDisplayGroup(
			array(
				'bmeCollectionId',
				'itemSelectMeta',
				'rulebox',
				'previewItemsButton',
				'itemPreviewDiv',
			),
			'bmeItemsSet',
			array(
				'legend' => __('Step 1: Select Items'),
				'class' => 'bmeFieldset',
		));

		$this->addDisplayGroup(
			array(
				'selectFields[]',
				'previewFieldsButton',
				'fieldPreviewDiv',
			),
			'bmeFieldsSet',
			array(
				'legend' => __('Step 2: Select Fields'),
				'class' => 'bmeFieldset',
		));

		$this->addDisplayGroup(
			array(
				'changesRadio',
				'previewChangesButton',
				'bmeSearch',
				'bmeRegexp',
				'bmeReplace',
				'bmeAdd',
				'bmePrepend',
				'bmeAppend',
				'bmeLtrim',
				'bmeRtrim',
				'bmeCaseconvert',
				'bmeExplode',
				'changesPreviewDiv',
			),
			'bmeChangesSet',
			array(
				'legend' => __('Step 3: Define Changes'),
				'class' => 'bmeFieldset',
		));

		$this->addDisplayGroup(
			array(
				'useBackgroundJob',
			),
			'bmeJob'
		);

		if (version_compare(OMEKA_VERSION, '2.2.1') >= 0)
			$this->addElement('hash', 'bulk_editor_token');

		$this->addElement('submit', 'performButton', array(
			'label' => __('Apply Edits Now'),
			'order' => 99,
		));
	}

	/**
	 * Overrides standard Omeka form behavior to tweak display
	 * and fix radio display eccentricity
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

			} elseif ($element->getAttrib('class') == 'elementHidden') {
				$element->getDecorator('FieldTag')->setOption('class', 'field bmeHidden');
				$id = $element->getAttrib('id');

				$element->getDecorator('FieldTag')->setOption('id', $id . '-field');


			} elseif ($element instanceof Zend_Form_Element_Hidden
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
		$options = get_table_options('Collection');
		unset($options['']);
		// Add the id of collections to simplify selection with similar names.
		array_walk($options, function (&$value, $key) {
			$value = '(#' . $key . ') ' . $value;
		});
		return array('0' => __('All Collections')) + $options;
	}

	/**
	 * Get an array to be used in html select input containing all elements.
	 *
	 * @return array $elementOptions Array of options for a dropdown
	 * menu containing all elements applicable to records of type Item
	 */
	private function _getElementOptions()
	{
		/*
		$options = get_table_options('Element', null, array(
			'record_types' => array('Item', 'All'),
			'sort' => 'alphaBySet')
		);
		unset($options['']);
		return $options;
		*/

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
		//		$options = array('' => __('Select Below'));
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
