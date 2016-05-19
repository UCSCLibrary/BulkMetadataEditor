<?php
/**
 * Helper to display or process bulk changes.
 *
 * @package BulkMetadataEditor
 */
class BulkMetadataEditor_View_Helper_BulkEdit extends Zend_View_Helper_Abstract
{

    protected $_db;

    public function __construct()
    {
        $this->_db = get_db();
    }

    /**
     * Get the helper.
     *
     * @return This view helper.
     */
    public function bulkEdit()
    {
        return $this;
    }

    /**
     * Retrieve Items matching selection rules.
     *
     * Retrieve items matching the rules contained in the params (generally from
     * POST data from the user input form).
     *
     * @param array $params
     * @param int $max Maximum number of items to return. If set to zero, all
     * matching items will be returned.
     * @param array $items Array of items matching the selection rules. Each
     * element of this array is itself an array containing identifying
     * information for a single matched item.
     * If max is empty, only ids are returned (max is used only for display).
     */
    public function getItems($params, $max = 0)
    {
        $select = $this->_getSelect($params);

        // Get only the item ids when there is no max.
        if (empty($max)) {
            $select
                ->reset(Zend_Db_Select::COLUMNS)
                ->columns(array("items.id"));
            try {
                $itemIds = $this->_db->fetchCol($select);
            } catch (Exception $e) {
                throw $e;
            }
            return $itemIds;
        }

        // Get the objects.
        $table = $this->_db->getTable('Item');
        $table->applyPagination($select, $max);
        $items = $table->fetchObjects($select);

        if (!$items) {
            return array();
        }

        // Generate the return array from all of the items if max is set.
        $itemsArray = array();
        foreach ($items as $item) {
            try {
                $itemsArray[] = $this->_pullItemData($item);
            } catch (Exception $e) {
                throw $e;
            }
        }

        return $itemsArray;
    }

    /**
     * Count the total of Items matching selection rules.
     *
     * Retrieve items matching the rules contained in the params (generally from
     * POST data from the user input form).
     *
     * @param array $params
     * @param integer
     */
    public function countItems($params)
    {
        $table = $this->_db->getTable('Item');
        $select = $this->_getSelect($params);
        $select
            ->reset(Zend_Db_Select::COLUMNS)
            ->from(array(), "COUNT(DISTINCT(items.id))")
            ->reset(Zend_Db_Select::ORDER)
            ->reset(Zend_Db_Select::GROUP)
            ->reset(Zend_Db_Select::LIMIT_COUNT)
            ->reset(Zend_Db_Select::LIMIT_OFFSET);
        $total = $table->fetchOne($select);
        return $total;
    }

    /**
     * Helper to get select from the Bulk Metadata Editor form.
     *
     * @param array $params
     * @return Omeka_Db_Select
     */
    private function _getSelect($params)
    {
        $rules = $this->_listItemRules($params);

        // All rules can be applied via the Omeaka core, except the case.
        // Consequently, get_records() is not used directly.
        $rulesWithoutCase = array();
        $rulesWithCase = array();
        foreach ($rules as $rule) {
            if (empty($rule['case'])) {
                $rulesWithoutCase[] = $rule;
            }
            else {
                $rulesWithCase[] = $rule;
            }
        }

        // Workaround to make the plugin runs with old Omeka releases.
        $rulesOmekaNew = array();
        if ($rulesWithoutCase && version_compare(OMEKA_VERSION, '2.5', '<')) {
            $oldRules = array(
                'contains',
                'is exactly',
                'does not contain',
                'is empty',
                'is not empty',
            );
            foreach ($rulesWithoutCase as $key => $rule) {
                if (!in_array($rule['type'], $oldRules)) {
                    $rulesOmekaNew[] = $rule;
                    unset($rulesWithoutCase[$key]);
                }
            }
        }

        $itemsParams = $rulesWithoutCase
            ? array('advanced' => $rulesWithoutCase)
            : array();

        // set up query parameters to select items from a given collection
        if (!empty($params['bmeCollectionId'])) {
            $itemsParams['collection'] = $params['bmeCollectionId'];
        }

        $table = $this->_db->getTable('Item');
        $select = $table->getSelectForFindBy($itemsParams);
        $this->_addRulesWithCase($select, $rulesWithCase);
        $this->_addRulesOldOmeka($select, $rulesOmekaNew);

        return $select;
    }

    /**
     * Helper to prepare the list of rules to select items.
     *
     * @param array $params
     * @return array List of rules.
     */
    private function _listItemRules($params)
    {
        $rules = array();

        if (!empty($params['itemSelectMeta'])) {
            $noCase = array(
                'is empty',
                'is not empty',
                // In mysql, regex are always case insensitive.
                'matches',
                'does not match',
            );
            foreach ($params['item-rule-elements'] as $key => $ruleElement) {
                $rule = array();
                $rule['element_id'] = $params['item-rule-elements'][$key];
                $rule['type'] = $params['item-compare-types'][$key];
                $rule['terms'] = urldecode($params['item-selectors'][$key]);

                // By default, mysql is case insensitive.
                $rule['case'] = !in_array($rule['type'], $noCase)
                    && (!isset($params['item-cases'][$key]) || $params['item-cases'][$key] != 'false');

                $rules[] = $rule;
            }
        }

        return $rules;
    }

    /**
     * Helper to specify the case in queries.
     *
     * @see Table_Item::_advancedSearch()
     *
     * @param Zend_Db_Select $select
     * @param array $simpleTerms
     * @return void
     */
    private function _addRulesWithCase($select, $rulesWithCase)
    {
            if (empty($rulesWithCase)) {
            return;
        }

        $db = $this->_db;
        $advancedIndex = 0;
        foreach ($rulesWithCase as $v) {
            // Do not search on blank rows.
            if (empty($v['element_id']) || empty($v['type'])) {
                continue;
            }

            $value = isset($v['terms']) ? $v['terms'] : null;
            $type = $v['type'];
            $elementId = (int) $v['element_id'];
            $alias = "_advanced_case_{$advancedIndex}";

            $inner = true;
            $extraJoinCondition = '';
            // Determine what the WHERE clause should look like.
            switch ($type) {
                case 'contains':
                    $predicate = "COLLATE UTF8_BIN LIKE " . $db->quote('%'.$value .'%');
                    break;
                case 'is exactly':
                    $predicate = 'COLLATE UTF8_BIN = ' . $db->quote($value);
                    break;
                case 'does not contain':
                    $extraJoinCondition = "AND {$alias}.text COLLATE UTF8_BIN LIKE " . $db->quote('%'.$value .'%');
                    $inner = false;
                    $predicate = "IS NULL";
                    break;
                case 'starts with':
                    $predicate = "COLLATE UTF8_BIN LIKE " . $db->quote($value.'%');
                    break;
                case 'ends with':
                    $predicate = "COLLATE UTF8_BIN LIKE " . $db->quote('%'.$value);
                    break;
                case 'is not exactly':
                    $predicate = 'COLLATE UTF8_BIN != ' . $db->quote($value);
                    break;
                default:
                    throw new Omeka_Record_Exception(__('Invalid search type given!'));
            }

            // Note that $elementId was earlier forced to int, so manual quoting
            // is unnecessary here
            $joinCondition = "{$alias}.record_id = items.id AND {$alias}.record_type = 'Item' AND {$alias}.element_id = $elementId";
            if ($extraJoinCondition) {
                $joinCondition .= ' ' . $extraJoinCondition;
            }
            if ($inner) {
                $select->joinInner(array($alias => $db->ElementText), $joinCondition, array());
            } else {
                $select->joinLeft(array($alias => $db->ElementText), $joinCondition, array());
            }
            $select->where("{$alias}.text {$predicate}");

            $advancedIndex++;
        }
    }

    /**
     * Helper to specify new rules in queries for Omeka releases < 2.5.
     *
     * @see Table_Item::_advancedSearch()
     *
     * @param Zend_Db_Select $select
     * @param array $simpleTerms
     * @return void
     */
    private function _addRulesOldOmeka($select, $rulesOmekaNew)
    {
        if (empty($rulesOmekaNew)) {
            return;
        }

        $db = $this->_db;
        $advancedIndex = 0;
        foreach ($rulesOmekaNew as $v) {
            // Do not search on blank rows.
            if (empty($v['element_id']) || empty($v['type'])) {
                continue;
            }

            $value = isset($v['terms']) ? $v['terms'] : null;
            $type = $v['type'];
            $elementId = (int) $v['element_id'];
            $alias = "_advanced_old_{$advancedIndex}";

            $inner = true;
            $extraJoinCondition = '';
            // Determine what the WHERE clause should look like.
            switch ($type) {
                case 'starts with':
                    $predicate = "LIKE " . $db->quote($value.'%');
                    break;
                case 'ends with':
                    $predicate = "LIKE " . $db->quote('%'.$value);
                    break;
                case 'is not exactly':
                    $predicate = ' != ' . $db->quote($value);
                    break;
                case 'matches':
                    if (!strlen($value)) {
                        continue 2;
                    }
                    $predicate = 'REGEXP ' . $db->quote($value);
                    break;
                case 'does not match':
                    if (!strlen($value)) {
                        continue 2;
                    }
                    $predicate = 'NOT REGEXP ' . $db->quote($value);
                    break;
                default:
                    throw new Omeka_Record_Exception(__('Invalid search type given!'));
            }

            // Note that $elementId was earlier forced to int, so manual quoting
            // is unnecessary here
            $joinCondition = "{$alias}.record_id = items.id AND {$alias}.record_type = 'Item' AND {$alias}.element_id = $elementId";
            if ($extraJoinCondition) {
                $joinCondition .= ' ' . $extraJoinCondition;
            }
            if ($inner) {
                $select->joinInner(array($alias => $db->ElementText), $joinCondition, array());
            } else {
                $select->joinLeft(array($alias => $db->ElementText), $joinCondition, array());
            }
            $select->where("{$alias}.text {$predicate}");

            $advancedIndex++;
        }
    }

    /**
     * Retrieves basic data about an Omeka item.
     *
     * @param Object $item Omeka item record object to pull data from
     * @return array The title, description, type and ID of the given item as an
     * associative array.
     */
    private function _pullItemData($item)
    {
        if (!$item instanceof Item) {
            throw new Exception(__('Cannot pull item data from a non-item.'));
        }
        $title = __('[untitled]');
        $description = __('[no description given]');
        $typename = __("[undefined]");
        $titles = $item->getElementTexts('Dublin Core', 'Title');
        if (count($titles) > 0) {
            $title = strip_formatting($titles[0]->text);
        }
        $descriptions = $item->getElementTexts('Dublin Core', 'Description');
        if (count($descriptions) > 0) {
            $description = strip_formatting($descriptions[0]->text);
        }
        $type = $item->getItemType();
        if (is_object($type)) {
            $typename = strip_formatting($type->name);
        }

        $rv = array(
            'id' => $item->id,
            'title' => $title,
            'description' => $description,
            'type' => $typename,
        );
        return $rv;
    }

    /**
     * Retrieve metadata elements matching selection rules.
     *
     * Retrieve metadata elements from the items provided which match the rules
     * contained in the params (generally from POST data from the user input
     * form).
     *
     * @param array $params
     * @param array $itemIds Array of items from whose metadata elements the
     * fields will be selected.
     * @param int $max Maximum number of items to return. If set to zero, all
     * matching items will be returned.
     * @return array $elements Array of elements matching the selection rules.
     * Each element of this array is itself an array containing identifying
     * information for a single matched metadata element.
     */
    public function getFields($params, $itemIds, $max = 0)
    {
        if (empty($itemIds)) {
            return array();
        }

        $fields = array();
        $newfields = array();

        if (!isset($params['selectFields'])) {
            $fields = $this->_getElementIds();
        } else {
            $fields = $params['selectFields'];
        }

        $i = 0;
        $j = 1;
        foreach ($itemIds as $itemId) {
            $i++;
            if ($max > 0 && $j > $max) {
                break;
            }

            try {
                $itemObj = get_record_by_id('Item', $itemId);
            } catch (Exception $e) {
                throw $e;
            }

            $titles = $itemObj->getElementTexts('Dublin Core', 'Title');
            $itemTitle = $titles ? strip_formatting($titles[0]->text) : __('[Untitled]');

            $flag = false;
            foreach ($fields as $field) {
                try {
                    $element = get_record_by_id('Element', $field);
                    $fieldname = $element->name;
                    $elementTexts = $itemObj->getElementTextsByRecord($element);
                } catch (Exception $e) {
                    throw $e;
                }
                foreach ($elementTexts as $elementText) {
                    $newfields[$itemId][] = array(
                        'id' => $elementText->id,
                        'field' => $fieldname,
                        'value' => $elementText->text,
                        'element_id' => $element->id,
                    );
                    $flag = true;
                }
            }
            if ($flag) {
                $newfields[$itemId]['title'] = $itemTitle;
                $j++;
            }
        }

        return $newfields;
    }

    /**
     * Retrieve Element Ids.
     *
     * Retrieve from the database the IDs of all elements applicable to records
     * of type Item
     *
     * @param void
     * @return array Array of all element IDs applicable to records of type
     * Item.
     */
    private function _getElementIds()
    {
        $db = $this->_db;
        $sql = "
        SELECT DISTINCT(e.id) as id
        FROM {$db->ElementSet} es
        JOIN {$db->Element} e ON es.id = e.element_set_id
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id
        WHERE es.record_type IS NULL OR es.record_type = 'Item' ";
        return $db->fetchCol($sql);
    }

    /**
     * Retrieve the edits specified by the params.
     *
     * Retrieve metadata element texts according to the rules in the params
     * (generally from POST variables set by the input form).
     *
     * @param array $params
     * @param integer $max Max change to get
     * @return array Array of items.
     */
    public function getChanges($params, $max = 0)
    {
        try {
            $items = $this->getItems($params);
            $fields = $this->getFields($params, $items);
            $changes = $this->_update($params, $items, $fields, $max, false);
        } catch (Exception $e) {
            throw $e;
        }
        return $changes;
    }

    /**
     * Perform the edits specified by the params.
     *
     * This function calls the matching subroutines with no maximum number of
     * results, and alerts the changes subroutine to perform the changes rather
     * than just displaying them.
     *
     * @param array $params
     * @return void
     */
    public function perform($params)
    {
        try {
            $items = $this->getItems($params);
            $fields = $this->getFields($params, $items);
            $this->_update($params, $items, $fields, 0, true);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve and/or perform bulk edits.
     *
     * Retrieve and optionally perform edits to metadata element texts according
     * to the rules in the params (generally from POST variables set by the
     * input form).
     *
     * @param array $params
     * @param array $itemIds Array of items on which to perform the edits. Each
     * element of this array is an array containing a single item's identifying
     * information. Only items in this array will be selected for editing.
     * @param array $fields Array of metadata elements on which to perform the
     * edits. Each element of this array is an array containing a single
     * elements's identifying information. Only elements included in this array
     * with be selected for editing.
     * @param int $max The maximum number of changes to return. If set to zero,
     * all changes will be returned.
     * @param bool $perform If true, the edits will be performed. Otherwise, the
     * changes defined by the params will be returned but the database will
     * remain unchanged.
     * @return array $changes An array containing the old and new values of
     * element text records which will be updated in the database.
     */
    private function _update($params, $itemIds, $fields, $max, $perform)
    {
        if (!isset($params['changesRadio'])) {
            throw new Exception(__('Please select an action to perform.'));
        }

        $changes = array();

        $i = 0;
        $j = 1;

        foreach ($itemIds as $itemId) {
            $i++;
            $made = array();
            if (!isset($fields[$itemId]) && $params['changesRadio'] != 'add') {
                continue;
            }

            $itemObj = get_record_by_id('Item', $itemId);
            if (empty($itemObj)) {
                continue;
            }

            if ($params['changesRadio'] == 'add') {
                $fieldItem = array();

                if (!isset($params['selectFields'])) {
                    $fields = $this->_getElementIds();
                } else {
                    $fields = $params['selectFields'];
                }
                foreach ($fields as $elementId) {
                    $fieldItem[] = array(
                        'element_id' => $elementId,
                    );
                }
            } else {
                $fieldItem = $fields[$itemId];
                unset($fieldItem['title']);
            }

            if ($max > 0 and $j > $max) {
                break;
            }

            // Regroup fields by element and deduplicate them before processing.
            if ($params['changesRadio'] == 'deduplicate') {
                $fieldsByElement = array();
                foreach ($fieldItem as $field) {
                    $fieldsByElement[$field['element_id']][$field['id']] = $field['value'];
                }
                $deduplicatedFieldsByElement = array();
                foreach ($fieldsByElement as $key => $element) {
                    $deduplicatedFieldsByElement[$key] = array_unique(array_filter(array_map('trim', $element)));
                }
            }

            // Deduplicate files.
            if ($params['changesRadio'] == 'deduplicate-files') {
                $this->_deduplicateFiles($itemObj);
                // No field to change, so process the next item.
                continue;
            }

            $titles = $itemObj->getElementTexts('Dublin Core', 'Title');
            $itemHasTitle = (boolean) $titles;
            $itemTitle = $itemHasTitle ? strip_formatting($titles[0]->text) : __('[Untitled]');
            foreach ($fieldItem as $field) {
                $message = __('Bulk Metadata Editor #%d [%s]:', $itemId, $params['changesRadio']) . ' ';
                $replaceType = 'normal';
                switch ($params['changesRadio']) {
                    case 'preg':
                        $replaceType = 'preg';
                        // No break.

                    case 'replace':
                        // expect a 'find' and 'replace' variable
                        if (!isset($params['bmeSearch']) || !isset($params['bmeReplace'])) {
                            // TODO:proper error handling
                            throw new Exception(__('Please define search and replace terms.'));
                        }

                        $element = $itemObj->getElementById($field['element_id']);
                        // $eText = $itemObj->getElementTextsByRecord($element);
                        $eText = get_record_by_id('ElementText', $field['id']);

                        $count = 0;

                        if ($replaceType == 'normal') {
                            $new = str_replace($params['bmeSearch'], $params['bmeReplace'], $eText->text, $count);
                        } elseif ($replaceType == "regexp") {
                            $new = preg_replace($params['bmeSearch'], $params['bmeReplace'], $eText->text, - 1, $count);
                        }

                        // if str_replace matches anything, update the return
                        // array.
                        if ($count > 0) {
                            $changes[] = array(
                                'itemId' => $itemId,
                                'item' => $itemTitle,
                                'field' => $element->name,
                                'old' => $eText->text,
                                'new' => $new,
                            );
                            if ($perform) {
                                $html = $new != strip_tags($new);
                                try {
                                    $eText->delete();
                                    if (strlen($new)) {
                                        $itemObj->addTextForElement($element, $new, $html);
                                    }
                                } catch (Exception $e) {
                                    $message .= __('An error occurred: %s', $e->getMessage());
                                    _log($message, Zend_Log::ERR);
                                    continue 2;
                                }
                            }
                            $j++;
                        }
                        break;

                    case 'delete':
                        // update the return array
                        try {
                            $element = $itemObj->getElementById($field['element_id']);
                            $eText = get_record_by_id('ElementText', $field['id']);
                        } catch (Exception $e) {
                            throw $e;
                        }

                        if (empty($element) || empty($eText)) {
                            throw new Exception(__('Error retrieving item data for deletion.'));
                        }

                        $new = '';
                        $changes[] = array(
                            'itemId' => $itemId,
                            'item' => $itemTitle,
                            'field' => $element->name,
                            'old' => $eText->text,
                            'new' => 'null',
                        );
                        if ($perform) {
                            try {
                                $eText->delete();
                            } catch (Exception $e) {
                                $message .= __('An error occurred: %s', $e->getMessage());
                                _log($message, Zend_Log::ERR);
                                continue 2;
                            }
                        }

                        $j++;
                        break;

                    case 'append':
                        if (!isset($params['bmeAppend']) || !strlen($params['bmeAppend'])) {
                            throw new Exception(__('Please input some text to append'));
                        }

                        if (!isset($params['delimiter'])) {
                            $params['delimiter'] = ' ';
                        }

                        try {
                            $element = $itemObj->getElementById($field['element_id']);
                            $eText = get_record_by_id('ElementText', $field['id']);

                            $new = $eText->text . $params['delimiter'] . $params['bmeAppend'];
                        } catch (Exception $e) {
                            throw $e;
                        }

                        $changes[] = array(
                            'itemId' => $itemId,
                            'item' => $itemTitle,
                            'field' => $element->name,
                            'old' => $eText->text,
                            'new' => $new,
                        );

                        if ($perform) {
                            $html = $new != strip_tags($new);
                            try {
                                $eText->delete();
                                $itemObj->addTextForElement($element, $new, $html);
                            } catch (Exception $e) {
                                $message .= __('An error occurred: %s', $e->getMessage());
                                _log($message, Zend_Log::ERR);
                                continue 2;
                            }
                        }
                        $j++;
                        break;

                    case 'add':
                        if (!isset($params['bmeAdd']) || !strlen($params['bmeAdd'])) {
                            throw new Exception(__('Please input some text to add.'));
                        }

                        try {
                            $element = $itemObj->getElementById($field['element_id']);
                        } catch (Exception $e) {
                            throw $e;
                        }

                        if (!in_array($field['element_id'], $made)) {
                            $new = $params['bmeAdd'];
                            $changes[] = array(
                                'itemId' => $itemId,
                                'item' => $itemTitle,
                                'field' => $element->name,
                                'old' => 'null',
                                'new' => $new,
                            );
                            if ($perform) {
                                $html = $new != strip_tags($new);
                                try {
                                    $itemObj->addTextForElement($element, $new, $html);
                                } catch (Exception $e) {
                                    $message .= __('An error occurred: %s', $e->getMessage());
                                    _log($message, Zend_Log::ERR);
                                    continue 2;
                                }
                            }
                            $j++;
                        }
                        $made[] = $field['element_id'];
                        break;

                    case 'deduplicate':
                        try {
                            $element = $itemObj->getElementById($field['element_id']);
                            $eText = get_record_by_id('ElementText', $field['id']);
                        } catch (Exception $e) {
                            throw $e;
                        }

                        if (empty($element) || empty($eText)) {
                            throw new Exception(__('Error retrieving item data for deduplication.'));
                        }

                        if (!isset($deduplicatedFieldsByElement[$element->id][$field['id']])) {
                            $new = '';
                            $changes[] = array(
                                'itemId' => $itemId,
                                'item' => $itemTitle,
                                'field' => $element->name,
                                'old' => $eText->text,
                                'new' => 'null',
                            );
                            if ($perform) {
                                try {
                                    $eText->delete();
                                } catch (Exception $e) {
                                    $message .= __('An error occurred: %s', $e->getMessage());
                                    _log($message, Zend_Log::ERR);
                                    continue 2;
                                }
                            }
                        }

                        $j++;
                        break;

                    case 'deduplicate-files':
                        // Nothing to do here.
                        break;
                } // end switch
            } // end field item loop
            try {
                $itemObj->saveElementTexts();
            } catch (Exception $e) {
                $message .= __('An error occurred: %s', $e->getMessage());
                _log($message, Zend_Log::ERR);
                continue;
            }
            if ($perform) {
                $message .= __('Success.');
                _log($message, Zend_Log::INFO);
            }
        } // end item loop

        return $changes;
    }

    /**
     * Remove all files of an item with the same hash, except the first.
     *
     * @param Item $item An existing item.
     * @return boolean Success or fail.
     */
    private function _deduplicateFiles($item)
    {
        // TODO Use a main sql to avoid to load each file.

        // Create the list of hashs.
        $hashs = array();
        $toDelete = array();
        $files = $item->Files;
        foreach ($files as $key => $file) {
            if (in_array($file->authentication, $hashs)) {
                $toDelete[] = $file;
            }
            // New hash.
            else {
                $hashs[] = $file->authentication;
            }
        }
        foreach ($toDelete as $file) {
            $file->delete();
        }

        return true;
    }
}
