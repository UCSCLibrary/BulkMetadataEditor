<?php echo head(array('title' => 'Bulk Metadata Search and Replace')); ?>

<?php echo flash(); ?>
<form id='sedmeta-form'>
<input type="hidden" name="callback" value=""/>
<fieldset class="sedmeta-fieldset" id='sedmeta-items-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 1: Select Items to Edit </h2>

   <div class="field">
   <input type="checkbox" name="item-selections" value="all" id="item-select-all" checked="checked">Apply edits to all items
   </div>
   <div class="field">
   <input type="checkbox" name="item-selections" value="all" id="item-select-collection" >Select items to edit based on their collection
   </div>
   <div class="field" id="item-collection-select" style="display:none;">
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('sedmeta-collection-id',null,array('id' => 'sedmeta-collection-id'),$this->form_collection_options); ?>
   </div>
   </div>

   <div class="field">
   <input type="checkbox" name="item-selections" value="all" id="item-select-meta" >Select items to edit based on their metadata
   </div>
   <div id="item-meta-selects" style="display:none;">
   <div class="field" id="item-meta-select-1">
   <p>Which also meet the following criteria: (use * as a wildcard character)</p>
   <div class="inputs three columns alpha">
   <?php echo $this->formSelect('sedmeta-element-id', '50', array('id' => 'sedmeta-element-id'), $this->form_element_options) ?>
   </div>
   <div class="inputs two columns beta">
   <?php echo $this->formSelect('sedmeta-compare', null, array('id' => 'sedmeta-compare'), $this->form_compare_options) ?>
   </div>
   <div class="inputs three columns omega">
   <?php echo $this->formText('sedmeta-selector',"Input search term here",array()) ?>
   </div>
   </div> 

   <button id="add-rule">Add Another Rule</button>
   </div>

<button id="preview-items-button">Preview Selected Items</button>
<button style="display:none" id="hide-item-preview">Hide Item Preview</button>

<div class="field" id="item-preview">
</div>

</fieldset>

<fieldset class="sedmeta-fieldset" id='sedmeta-fields-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 3: Select Fields to Edit </h2>

   <input type="radio" name="field-selections" value="all" id="field-select-all" checked="checked">Apply edits to all fields<br>
   <input type="radio" name="field-selections" value="select" id="field-select-some" >Select fields to edit from a list<br>

   <div class="field" id="field-select-list" style="display:none;">
   <div class="inputs four columns omega">
   <?php echo $this->formSelect('selectfields[]',null,array('id' => 'sedmeta-select-fields','size' => '10'),$this->form_element_options); ?>
   </div> 
   </div>

<button id="preview-fields-button">Preview Selected Fields</button>
<button style="display:none" id="hide-field-preview">Hide Field Preview</button>
   
<div class="field" id="field-preview">
</div>

</fieldset>

<fieldset class="sedmeta-fieldset" id='sedmeta-changes-set' style="border: 1px solid black; padding:15px; margin:10px;">
   <h2>Step 3: Define Edits </h2>

   <div class="field">
   <input type="radio" name="changes-radio" value="replace" id="changes-replace-radio" >Search and replace text (within any metadata in the selected fields on the selected items)
   </div>

   <div id='changes-replace' style="display:none">
  <div class="field">
   <div id="sedmeta-find-label" class="two columns alpha">
   <label for="sedmeta-find"><?php echo __('Search for:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('sedmeta-find',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to search for ' ); ?></p>
   </div>
   </div>
   <div class="field">
   <div id="sedmeta-replace-label" class="two columns alpha">
   <label for="sedmeta-replace"><?php echo __('Replace with:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('sedmeta-replace',"", array()); ?>
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
   <div id="sedmeta-add-label" class="two columns alpha">
   <label for="sedmeta-add"><?php echo __('Text to Add:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('sedmeta-add',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to add as metadata' ); ?></p>
   </div>
   </div>
</div>


   <div class="field">
   <input type="radio" name="changes-radio" value="append" id="changes-append-radio" />Append text to existing metadata in the selected fields
   </div>

     <div id='changes-append' style="display:none">
  <div class="field">
   <div id="sedmeta-append-label" class="two columns alpha">
   <label for="sedmeta-append"><?php echo __('Text to Append:'); ?></label>
   </div>
   <div class="inputs four columns omega">
   <?php echo $this->formText('sedmeta-append',"", array()); ?>
   <p class="explanation"><?php echo __( 'Input text you want to append to metadata' ); ?></p>
   </div>
   </div>
   </div>


   <div class="field">
   <input type="radio" name="changes-radio" value="delete" id="changes-delete-radio">Delete all existing metadata in the selected fields
   </div>

<button id="preview-changes-button">Preview Changes</button>
<button style="display:none" id="hide-changes-preview">Hide Preview of Changes</button>
 
<div class="field" id="changes-preview">
</div>

</fieldset>
<button type="submit">Apply edits now</button>

</form>
<?php
   
if(isset($_REQUEST['sedmeta-find'])&&isset($_REQUEST['sedmeta-replace']))
  {
    //TODO check nonce
      $toFind = $_REQUEST['sedmeta-find'];
      $toReplace = $_REQUEST['sedmeta-replace'];

      $params = array();
      $matchText = '/.*/';
      $compare=false;
      $selectElementID = $_REQUEST['sedmeta-element-id'];
      $compareType = $_REQUEST['sedmeta-compare'];
      $selector = $_REQUEST['sedmeta-selector'];
/*
      if(isset($selectElementID)&&isset($compareType)&&isset($selector)&&$selector!=="Input search term here")
	{
	  $compare=true;
	  //TODO escape special characters in compare string
	  $negsearch = false;
	    
	  switch($compare)
	    {
	    case "exact":
	      $matchText='/^'.$compareType.'$/';
	      break;

	    case "contains":
	      $matchText='/'.$compareType.'/';
	      break;

	    case "!exact":
	      $negsearch = true;
	      $matchText='/^'.$compareType.'$/';
	      break;

	    case "!contains":
	      $negsearch = true;
	      $matchText='/'.$compareType.'/';
	      break;

	    }
	}

      $cid=$_REQUEST['sedmeta-collection-id'];
      if( isset($cid) && $cid != 0)
	$params['collection']=$_REQUEST['sedmeta-collection-id'];

      $replaceElements = -1;
      if(isset($_REQUEST['sedmeta-replace-fields']))
        $replaceElements = $_REQUEST['sedmeta-replace-fields'];

      $items = get_records("Item",$params,0);
      //echo("<br>Items:  ".count($items));

      foreach($items as $item)
	{
	  $matched = false;
	  if($compare)
	    {
	      $compareTexts = $item->getElementTextsByRecord($item->getElementById($selectElementID));
	      foreach($compareTexts as $compareText)
		{
		  $match = preg_match($matchText,$compareText->text);
		  if ($match===false)
		    die('regular expression error!');//TODO proper error handling

		  if($negsearch)
		    $match = !(boolean)$match;
	      
		  if($match)
		    {
		      $matched=true;
		      break;
		    }
		}
	      if(!$matched)
		continue;
	    }

	  $newElementTexts=array();
	  $elementTexts = $item->getAllElementTexts();

	  foreach($elementTexts as $elementText)
	    {
	      
	      if($replaceElements == -1 || in_array($elementText->element_id,$replaceElements))
		{
		  $newElementTexts[] = array(
					     'element_id' => $elementText->element_id,
					     'text' => str_replace($toFind,$toReplace,$elementText->text),
					     'html' =>  str_replace($toFind,$toReplace,$elementText->html)
					     );

		  
		  
		} else {
	        $newElementTexts[] = array(
					   'element_id' => $elementText->element_id,
					   'text' => $elementText->text,
					   'html' => $elementText->html
					   );
	      
	      }
	    }
	  //print_r($newElementTexts);
	  //echo "<br> Element Texts: ".count($newElementTexts)."</br>";

	  $item->deleteElementTexts();
	  $item->addElementTextsByArray($newElementTexts);
	  $item->saveElementTexts();

	}

      //echo('<div class="happybox"><h2>Metadata successfully edited!</h2></div>');
      */
  }
?>

</form>
<?php echo foot(); ?>