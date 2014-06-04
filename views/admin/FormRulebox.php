<div id="item-meta-selects" style="display:none;">
   <div class="field" id="item-meta-select">
   <p>Only select items which also meet the following criteria: (use * as a wildcard character)</p>
   <div id="item-rule-boxes">
   <div id="item-rule-box" class="item-rule-box" style="clear:left;">
   <div class="inputs three columns alpha">
   <?php echo $this->formSelect('sedmeta-element-id', '50', array('class' => 'sedmeta-element-id'), $this->form_element_options) ?>
   </div>
   <div class="inputs two columns beta">
   <?php echo $this->formSelect('sedmeta-compare', null, array('class' => 'sedmeta-compare'), $this->form_compare_options) ?>
   </div>
   <div class="inputs three columns omega">
   <?php echo $this->formText('sedmeta-selector',"Input search term here",array('class'=>'sedmeta-selector')) ?>
   </div>
  <div class="removeRule">[x]</div>
   <div class="field">
   <div class="inputs two columns omega">
  <?php echo $this->formCheckbox('sedmeta-case',"Match Case",array('class'=>'sedmeta-case','checked'=>'checked')) ?><label for="sedmeta-case"> Match Case </label>
   </div>
   </div>
   </div>	     
   </div>
   </div> 
   <div class="field">
   <button id="add-rule">Add Another Rule</button>
   </div>
   </div>