<?php 
/**
 * BulkMetadataEditor control panel view
 *
 * This view delivers a form to be displayed on the Omeka Curator Dashboard
 * collecting input from curators to define bulk edits to perform on the
 * omeka database.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */



echo head(array('title' => 'Bulk Metadata Search and Replace')); ?>

<?php echo flash(); ?>
<form id='bulk-metadata-editor-form'>

<input type="hidden" name="callback" value=""/>

<fieldset class="bulk-metadata-editor-fieldset" id='bulk-metadata-editor-items-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 1: Select Items to Edit </h2>

   <div class="field">
  <p>Edit items from the following collection:</p>
   </div>
   <div class="field" id="item-collection-select">
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('bulk-metadata-editor-collection-id',null,array('id' => 'bulk-metadata-editor-collection-id'),$this->form_collection_options); ?>
   </div>
   </div>

   <div class="field">
   <input type="checkbox" name="item-select-meta" id="item-select-meta" >Select items to edit based on their metadata
   </div>

   <div id="item-meta-selects" style="display:none;">
   <div class="field" id="item-meta-select">
   <p>Which also meet the following criteria: (use * as a wildcard character)</p>
   <div id="item-rule-boxes">
   <div id="item-rule-box" class="item-rule-box" style="clear:left;">
   <div class="inputs three columns alpha">
   <?php echo $this->formSelect('bulk-metadata-editor-element-id', '50', array('class' => 'bulk-metadata-editor-element-id'), $this->form_element_options) ?>
   </div>
   <div class="inputs two columns beta">
   <?php echo $this->formSelect('bulk-metadata-editor-compare', null, array('class' => 'bulk-metadata-editor-compare'), $this->form_compare_options) ?>
   </div>
   <div class="inputs three columns omega">
   <?php echo $this->formText('bulk-metadata-editor-selector',"Input search term here",array('class'=>'bulk-metadata-editor-selector')) ?>
   </div>
  <div class="removeRule">[x]</div>
   <div class="field">
   <div class="inputs two columns omega">
  <?php echo $this->formCheckbox('bulk-metadata-editor-case',"Match Case",array('class'=>'bulk-metadata-editor-case','checked'=>'checked')) ?><label for="bulk-metadata-editor-case"> Match Case </label>
   </div>
   </div>
   </div>	     
   </div>
   </div> 
   <div class="field">
   <button id="add-rule">Add Another Rule</button>
   </div>
   </div>

<div class="field">
<button class="preview-button" id="preview-items-button">Preview Selected Items</button>
<button style="display:none" id="hide-item-preview">Hide Item Preview</button>
</div>

<div class="field" id="item-preview">
</div>

</fieldset>

<fieldset class="bulk-metadata-editor-fieldset" id='bulk-metadata-editor-fields-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 2: Select Fields to Edit </h2>

   <div class="field" id="field-select-list" >
   <div class="inputs four columns omega">
   <?php echo $this->formSelect('selectfields[]',null,array('id' => 'bulk-metadata-editor-select-fields','size' => '10'),$this->form_element_options); ?>
   </div> 
   </div>

<div class="field">
<button class="preview-button" id="preview-fields-button">Preview Selected Fields</button>
<button style="display:none" id="hide-field-preview">Hide Field Preview</button>   
</div>

<div class="field" id="field-preview">
</div>

</fieldset>

<fieldset class="bulk-metadata-editor-fieldset" id='bulk-metadata-editor-changes-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 3: Define Edits </h2>

   <div class="field">
   <input type="radio" name="changes-radio" value="replace" id="changes-replace-radio" >Search and replace text (within any metadata in the selected fields on the selected items)
   </div>

   <div id='changes-replace' style="display:none">
  <div class="field">
   <div id="bulk-metadata-editor-find-label" class="two columns alpha">
   <label for="bulk-metadata-editor-find"><?php echo __('Search for:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('bulk-metadata-editor-find',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to search for ' ); ?></p>
   </div>
   </div>
   <div class="field">
   <div id="bulk-metadata-editor-replace-label" class="two columns alpha">
   <label for="bulk-metadata-editor-replace"><?php echo __('Replace with:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('bulk-metadata-editor-replace',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to replace with ' ); ?></p>
   </div>
   </div>
   <div class="field">
   <input type="checkbox" name="regexp" value="true" />Use regular expressions
   </div>
</div>
   <div class="field">
   <input type="radio" name="changes-radio" value="add" id="changes-add-radio">Add a new metadatum in the selected field
   </div>

   <div id='changes-add' style="display:none">
  <div class="field">
   <div id="bulk-metadata-editor-add-label" class="two columns alpha">
   <label for="bulk-metadata-editor-add"><?php echo __('Text to Add:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('bulk-metadata-editor-add',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to add as metadata' ); ?></p>
   </div>
   </div>
</div>
   <div class="field">
   <input type="radio" name="changes-radio" value="append" id="changes-append-radio" />Append text to existing metadata in the selected fields
   </div>

     <div id='changes-append' style="display:none">
  <div class="field">
   <div id="bulk-metadata-editor-append-label" class="two columns alpha">
   <label for="bulk-metadata-editor-append"><?php echo __('Text to Append:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('bulk-metadata-editor-append',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to append to metadata' ); ?></p>
   </div>
   </div>
   </div>

   <div class="field">
   <input type="radio" name="changes-radio" value="delete" id="changes-delete-radio">Delete all existing metadata in the selected fields
   </div>
<div class="field">
<button class="preview-button" id="preview-changes-button">Preview Changes</button>
<button style="display:none" id="hide-changes-preview">Hide Preview of Changes</button>
</div>
 
<div class="field" id="changes-preview">
</div>



</fieldset>
<div class="field">
<button type="submit" name="perform-button">Apply edits now</button>
</div>

<?php
   $nonce = new Zend_Form_Element_Hash('bulk-metadata-editor-admin-nonce'); 
echo $nonce->render();
?>
</form>
<?php echo foot(); ?>