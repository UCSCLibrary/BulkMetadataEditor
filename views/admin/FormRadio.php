    <div class="<?php echo $this->class ?>">
        <?php echo $this->formLabel($this->element->getName(),
				    $this->element->getLabel(),
				    array("class"=>'optional')) ?>

       <div class="inputs six columns omega">
        <p class="explanation"><?php echo $this->element->getDescription() ?></p>

       <?php 
       $options = $this->element->getMultiOptions();

foreach ($options as $option=>$value)
  {
    echo '<field id="'.$this->element->getName().'-'.$option.'-field">';
    echo ('<input type="radio" name="'.$this->element->getName().'" value="'.$option.'" id="'.$this->element->getName().'-'.$option.'">');
    echo ($value);
    echo "</field>";

  }
?>
        <?php echo $this->{$this->element->helper}(

            $this->element->getName(),
            $this->element->getValue(),
            $this->element->getAttribs()
        ) ?>
        <?php echo $this->formErrors($this->element->getMessages()) ?>

    </div>
</div>