<?php

// die('form: '.$this->_getElementOptions());
try {
    $db = get_db();
    $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, e.name AS element_name, it.name AS item_type_name
        FROM {$db->ElementSet} es
        JOIN {$db->Element} e ON es.id = e.element_set_id
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id
        WHERE es.record_type IS NULL OR es.record_type = 'Item'
        ORDER BY es.name, it.name, e.name";
    $elements = $db->fetchAll($sql);
} catch (Exception $e) {
    throw $e;
}
$form_element_options = array();
foreach ($elements as $element) {
    $optGroup = $element['item_type_name']
        ? __('Item Type') . ': ' . __($element['item_type_name'])
        : __($element['element_set_name']);
    $form_element_options[$optGroup][$element['element_id']] = __($element['element_name']);
}

$form_compare_options = array(
    'is exactly' => __('is exactly'),
    'is not exactly' => __('is not exactly'),
    'contains' => __('contains'),
    'does not contain' => __('does not contain'),
    'is empty' => __('is empty'),
    'is not empty' => __('is not empty'),
    'starts with' => __('starts with'),
    'ends with' => __('ends with'),
    'matches' => __('matches'),
    'does not match' => __('does not matches'),
);
?>
<div id="item-meta-selects" style="display: none;">
    <div class="field" id="item-meta-select">
        <p class="explanation"><?php echo __('Only select items which also meet the following criteria:'); ?></p>
        <div id="item-rule-boxes">
            <div id="item-rule-box" class="item-rule-box" style="clear: left;">
                <div class="inputs two columns alpha">
                    <?php echo $this->formSelect('bulk-metadata-editor-element-id', '50', array('class' => 'bulk-metadata-editor-element-id'), $form_element_options); ?>
                </div>
                <div class="inputs two columns">
                    <?php echo $this->formSelect('bulk-metadata-editor-compare', null, array('class' => 'bulk-metadata-editor-compare'), $form_compare_options); ?>
                </div>
                <div class="inputs three columns">
                    <?php echo $this->formText('bulk-metadata-editor-selector', '',
                        array('class'=>'bulk-metadata-editor-selector', 'placeholder' => __('Input search term here'))); ?>
                </div>
                <div class="inputs one column">
                    <label for="bulk-metadata-editor-case"><?php echo __('Match Case'); ?></label>
                    <?php echo $this->formCheckbox('bulk-metadata-editor-case', 'Match Case', array('class' => 'bulk-metadata-editor-case')); ?>
                </div>
                <div class="inputs one column omega">
                    <div class="removeRule" style="float:right;">[x]</div>
                </div>
            </div>
        </div>
    </div>
    <div class="field">
        <button id="add-rule"><?php echo __('Add Another Rule'); ?></button>
    </div>
</div>
