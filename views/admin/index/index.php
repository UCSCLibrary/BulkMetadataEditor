<?php

$head = array('bodyclass' => 'sed-meta primary', 
              'title' => html_escape(__('Bulk Metadata Editor')));
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>