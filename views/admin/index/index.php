<?php
$title =  __('Bulk Metadata Editor');
$head = array(
    'title' => $title,
    'bodyclass' => 'primary bulk-metadata-editor',
);
echo head($head);
?>
<?php echo flash(); ?>
<?php echo $form; ?>
<?php echo foot();
