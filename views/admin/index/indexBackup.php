<?php echo head(array('title' => 'Bulk Metadata Search and Replace')); ?>

<?php echo flash(); ?>
<form>
<fieldset>
<div class="field">
    <div id="sedmeta-find-label" class="two columns alpha">
        <label for="sedmeta-find"><?php echo __('Search for:'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo $this->formText('sedmeta-find',"", array()); ?>
        <p class="explanation"><?php echo __( 'Input text you want to search for ' ); ?></p>
    </div>
</div>
<div class="field">

    <div id="sedmeta-replace-label" class="two columns alpha">
        <label for="sedmeta-replace"><?php echo __('Replace with:'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo $this->formText('sedmeta-replace',"", array()); ?>
        <p class="explanation"><?php echo __( 'Input text you want to replace with ' ); ?></p>
    </div>
</div>
</fieldset>

<div class="field" style="padding:10px;">
   <input type="radio" name="select-item" id="select-item-all" value="all" checked="checked"/>Edit all <strong>items</strong>
<input type="radio" name="select-item" id="select-item-some" value="some"/>Select which <strong>items</strong> to edit
</div>

<fieldset class="sedmeta-fieldset" id='sedmeta-select-items' style="border: 1px solid black; padding:15px; margin:10px;display:none;">
<h2>Select Items to Edit</h2>
   <p>Edit the metadata of items in the following collection(s):</p>

   <div class="field">
   <div class="inputs three columns omega">
   <?php echo $this->formSelect('sedmeta-collection-id',null,array('id' => 'sedmeta-collection-id'),$this->form_collection_options); ?>
   </div>
   </div>

   <div class="field">
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

</fieldset>


<div class="field" style="padding:10px;">
   <input type="radio" name="select-field" id="select-field-all" value="all" checked="checked"/>Edit all <strong>fields</strong>
<input type="radio" name="select-field" id="select-field-some" value="some"/>Select which <strong>fields</strong> to edit
</div>


<fieldset class="sedmeta-fieldset" id='sedmeta-select-fields' style="border: 1px solid black; padding:15px; margin:10px;display:none;">
<h2>Select Fields to Edit </h2>
   <p>Select multiple fields by holding the ctrl, shift, and/or command keys</p>
   <div class="field">
   <div class="inputs four columns omega">
   <?php echo $this->formSelect('sedmeta-replace-fields[]',null,array('id' => 'sedmeta-select-fields','size' => '10'),$this->form_element_options); ?>
   </div> 
   </div>
   
</fieldset>
<button type="submit">Replace now</button>


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
      echo("<br>Items:  ".count($items));

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
	  echo "<br> Element Texts: ".count($newElementTexts)."</br>";

	  $item->deleteElementTexts();
	  $item->addElementTextsByArray($newElementTexts);
	  $item->saveElementTexts();

	}

       	echo('<div class="happybox"><h2>Metadata successfully edited!</h2></div>');
  }
?>

</form>
<?php echo foot(); ?>