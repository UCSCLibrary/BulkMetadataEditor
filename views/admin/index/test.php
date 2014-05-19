<?php

$head = array('bodyclass' => 'history-log primary', 
              'title' => html_escape(__('History Log | Create Log Report')));
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot(); ?>