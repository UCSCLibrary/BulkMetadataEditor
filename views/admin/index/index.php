<?php echo head(array('title' => 'Library of Congress Suggest')); ?>

<?php echo flash(); ?>
<form>
<div class="field">
    <div id="sedmeta-find-label" class="two columns alpha">
        <label for="sedmeta-find"><?php echo __('Search for:'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo get_view()->formText('sedmeta-find',"", array()); ?>
        <p class="explanation"><?php echo __( 'Input text you want to search for ' ); ?></p>
    </div>
</div><div class="field">

    <div id="sedmeta-replace-label" class="two columns alpha">
        <label for="sedmeta-replace"><?php echo __('Replace with:'); ?></label>
    </div>
    <div class="inputs five columns omega">
        <?php echo get_view()->formText('sedmeta-replace',"", array()); ?>
        <p class="explanation"><?php echo __( 'Input text you want to replace with ' ); ?></p>
    </div>
</div>
<div class="field">

<button type="submit">Replace now</button>
</div>
</form>
<?php
if(isset($_REQUEST['sedmeta-find'])&&isset($_REQUEST['sedmeta-replace']))
  {
    //TODO check nonce
      $toFind = $_REQUEST['sedmeta-find'];
      $toReplace = $_REQUEST['sedmeta-replace'];

      $items = get_records("Item",array());
      //$items = array(get_record_by_id("Item",2));
 
      foreach($items as $item)
	{
	  
	  $newElementText=array();
	  $newElementTexts=array();
	  $elementTexts = $item->getAllElementTexts();
	  foreach($elementTexts as $elementText)
	    {
	      $newElementTexts[] = array(
				      'element_id' => $elementText->element_id,
				      'text' => str_replace($toFind,$toReplace,$elementText->text),
				      'html' =>  str_replace($toFind,$toReplace,$elementText->html)
				      );
	    }

	$item->deleteElementTexts();
	$item->addElementTextsByArray($newElementTexts);

	}

	$item->saveElementTexts();
       	echo('<div class="happybox"><h2>Metadata successfully edited!</h2></div>');
  }
?>
<?php echo foot(); ?>