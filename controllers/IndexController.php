<?php
/**
 * SedMeta
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * The Sedmeta index controller class.
 *
 * @package SedMeta
 */
class SedMeta_IndexController extends Omeka_Controller_AbstractActionController
{    
    
    public function indexAction()
    {
      $this->view->form_element_options = $this->_getFormElementOptions();
      $this->view->form_compare_options = $this->_getFormCompareOptions();
      $this->view->form_collection_options = $this->_getFormCollectionOptions();
    }

    /**
     * Get an array to be used in formSelect() containing all elements.
     * 
     * @return array
     */
    private function _getFormElementOptions()
    {
        $db = $this->_helper->db->getDb();
        $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, e.name AS element_name, 
        it.name AS item_type_name, ls.id AS lc_suggest_id 
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
        LEFT JOIN {$db->LcSuggest} ls ON e.id = ls.element_id 
        WHERE es.record_type IS NULL OR es.record_type = 'Item' 
        ORDER BY es.name, it.name, e.name";
        $elements = $db->fetchAll($sql);
        $options = array();
	//        $options = array('' => __('Select Below'));
        foreach ($elements as $element) {
            $optGroup = $element['item_type_name'] 
                      ? __('Item Type') . ': ' . __($element['item_type_name']) 
                      : __($element['element_set_name']);
            $value = __($element['element_name']);
            if ($element['lc_suggest_id']) {
                $value .= ' *';
            }
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }

    /**
     * Get an array to be used in formSelect() containing all collections.
     * 
     * @return array
     */
    private function _getFormCollectionOptions()
    {
      $collections = get_records('Collection',array(),'0');
      $options = array('0'=>'All Collections');
      foreach ($collections as $collection)
	{
	  $title = $collection->getElementTexts('Dublin Core','Title')[0];
	  $options[$collection->id]=$title;


	}
      return $options;
    }

    /**
     * Get an array to be used in formSelect() containing all 
     *  comparison operators supported by this plugin.
     * 
     * @return array
     */
    private function _getFormCompareOptions()
    {
      $options = array(
		       'exact' => 'is exactly',
		       'contains' => 'contains',
		       '!exact' => 'is not exactly',
		       '!contains' => 'does not contain'
		       );
        return $options;
    }
    
    
}
