<?php
/**
 * SedMeta Index Controller class file
 *
 * This controller enforces selection rules on items and metadata 
 * elements, and performs various builk editing operations
 * on the database of element texts associated with Omeka items.
 * This class contains the bulk of the functionality of the
 * Sedmeta plugin.
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



  /**
   * Process form data in and prepare to display the main SedMeta form.
   *
   * If the form has been submitted, it performs the relevant changes.
   * Regardless, it populates the arrays of options for the view's
   * dropdown menus.
   *
   * @param void
   * @return void
   */
    public function indexAction()
    {
      //if the submit button was pushed
      if(isset($_REQUEST['perform-button']))
	{
	  

	  $flashMessenger = $this->_helper->FlashMessenger;
	  $message = 'The requested changes have been applied to the database';
	  $status = 'success';

	  try
	    {
	      //this will edit the database, so we should validate 
	      //our form input carefully
	      $this->_validateForm();
	      $this->_perform();

	    } catch (Exception $e) 
		{
		  $message = $e->getMessage();
		  $status = 'error';
		}
	  
	  $flashMessenger->addMessage($message,$status);
	  
	}
      
      //prepare options arrays for the view's dropdown menus
      $this->view->form_element_options = $this->_getFormElementOptions();
      $this->view->form_compare_options = $this->_getFormCompareOptions();
      $this->view->form_collection_options = $this->_getFormCollectionOptions();
    }

    /**
     * Retrieves selected items for preview.
     *
     * Retrieves the first few items matching the selection rules
     * and sends them to the view to be served to a browser preview script
     *
     * @param void
     * @return void
     */
    public function itemsAction()
    {
      $max= $this->_getParam('max');
      $this->view->items = $this->_getItems($max);
    }



    /**
     * Retrieves number of selected items for preview.
     *
     * Retrieves the number of items matching the selection rules
     * and sends them to the view to be served to a browser preview script
     *
     * param void
     * return void
     */
    public function countitemsAction()
    {
      $this->view->count = count($this->_getItems());
    }



    /**
     * Retrieves the selected metadata elements for preview.
     *
     * Retrieves the number of items matching the selection rules
     * and sends them to the view to be served to a browser preview script
     *
     * param void
     * return void
     *
     */
    public function fieldsAction()
    {
      $max=$this->_getParam('max');
      $this->view->fields = $this->_getFields($this->_getItems(),$max);
    }

    /**
     * Retrieves the first few edits defined by the form input.
     *
     * Retrieves the first few changes defined by the form input
     * and sends them to the view to be served to a browser preview script
     *
     * param void
     * return void
     */
    public function changesAction()
    {
      $max=$this->_getParam('max');
      $items=$this->_getItems();
      $fields=$this->_getFields($items);
      $this->view->changes = $this->_getChanges($items,$fields,$max,false);
    }



    /**
     * Perform the edits specified by the form input.
     *
     * This function calls the matching subroutines
     * with no maximum number of results, and 
     * alerts the changes subroutine to perform the 
     * changes rather than just displaying them. 
     */
    private function _perform()
    {
      $max = 0;
      $items=$this->_getItems();
      $fields=$this->_getFields($items);
      $this->_getChanges($items,$fields,$max,true);
    }

    /**
     * Get an array to be used in formSelect() containing all elements.
     * 
     * @param void
     * @return array $elementOptions Array of options for a dropdown
     * menu containing all elements applicable to records of type Item
     */
    private function _getFormElementOptions()
    {
        $db = $this->_helper->db->getDb();
        $sql = "
        SELECT es.name AS element_set_name, e.id AS element_id, 
        e.name AS element_name, it.name AS item_type_name
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
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
            
            $options[$optGroup][$element['element_id']] = $value;
        }
        return $options;
    }

    /**
     *Retrieve Element Ids.
     *
     *Retrieve from the database the IDs of all elements applicable
     *to records of type Item
     *
     *@param void
     *@return array $elementIds Array of all element IDs applicable to 
     *records of type Item.
     */
    private function _getElementIds()
    {
      $db = $this->_helper->db->getDb();
        $sql = "
        SELECT DISTINCT(e.id) as id 
        FROM {$db->ElementSet} es 
        JOIN {$db->Element} e ON es.id = e.element_set_id 
        LEFT JOIN {$db->ItemTypesElements} ite ON e.id = ite.element_id 
        LEFT JOIN {$db->ItemType} it ON ite.item_type_id = it.id 
        WHERE es.record_type IS NULL OR es.record_type = 'Item' ";
        $ids = $db->fetchAll($sql);
	$rv=array();
	foreach($ids as $id)
	  $rv[]=$id['id'];
        return $rv;
    }



    /**
     * Get an array to be used in formSelect() containing all collections.
     * 
     * @param void
     * @return array $collectionOptions Array of all collections and their
     * IDs, which will be used to populate a dropdown menu on the main view
     */
    private function _getFormCollectionOptions()
    {
      $collections = get_records('Collection',array(),'0');
      $options = array('0'=>'All Collections');
      foreach ($collections as $collection)
	{
	  $titles = $collection->getElementTexts('Dublin Core','Title');
	  if(isset($titles[0]))
	    $title = $titles[0];
	  $options[$collection->id]=$title;
	}

      return $options;
    }



    /**
     * Populate array for comparison operator dropdown
     *
     * Retrieve  an array to be used in formSelect() containing all 
     *  comparison operators supported by this plugin.
     * 
     * @param void
     * @return array $compareOptions Array of supported comparison operators
     */
    private function _getFormCompareOptions()
    {
      $options = array(
		       'exact' => 'is exactly',
		       'contains' => 'contains',
		       '!exact' => 'is not exactly',
		       '!contains' => 'does not contain',
		       'regexp'=>'matches regular expression'
		       );
        return $options;
    }



    /**
     * Retrieve and/or perform bulk edits.
     *
     * Retrieve and optionally perform edits to metadata element
     * texts according to the rules in the POST variables set 
     * by the input form.
     *
     * @param array $items Array of items on which to perform the
     * edits. Each element of this array is an array containing 
     * a single item's identifying information. Only items in this
     * array will be selected for editing.
     *
     * @param array $fields Array of metadata elements on which to 
     * perform the edits. Each element of this array is an array 
     * containing a single elements's identifying information.
     * Only elements included in this array with be selected for
     * editing.
     *
     * @param int $max The maximum number of changes to return.
     * If set to zero, all changes will be returned.
     *
     * @param bool $perform If true, the edits will be performed.
     * Otherwise, the changes defined by the form input will be 
     * returned but the database will remain unchanged.
     * 
     * @return array $changes An array containing the old and new 
     * values of element text records which will be updated
     * in the database.
     */
    private function _getChanges($items,$fields,$max,$perform)
    {
      if(!isset($_REQUEST['changes-radio']))
	die("Please select an action to perform");//TODO:proper error handling
      $changes=array();

      $j=1;
      $i=0;

      foreach($items as $item)
	{
	  $i++;
	  if($item['id']==0)
	    break;
	  $made=array();
	  if(empty($item) || !isset($fields[$item['id']]) )
	    continue;
	  $itemObj=get_record_by_id('Item',$item['id']);

	  $fieldItem = $fields[$item['id']];
	  
	  unset($fieldItem['title']);
	  
	  if($max>0 and $j>$max) break;
	  foreach($fieldItem as $field)
	    {
	      $replaceType="normal";
	      switch($_REQUEST['changes-radio'])
		{
		case 'preg':
		  $replaceType="preg";

		case 'replace':

		  //expect a 'find' and 'replace' variable
		  if(!isset($_REQUEST['sedmeta-find'])||!isset($_REQUEST['sedmeta-replace']))
		    die("ERROR! variables not set or something!");//TODO:proper error handling

		  $element=$itemObj->getElementById($field['elementID']);
		  //$eText = $itemObj->getElementTextsByRecord($element);
		  $eText = get_record_by_id('ElementText',$field['id']);
		      
		  $count=0;

		  if($replaceType=="normal")
		    $new=str_replace($_REQUEST['sedmeta-find'],$_REQUEST['sedmeta-replace'],$eText->text,$count);
		  elseif($replaceType=="regexp")
		    $new=preg_replace($_REQUEST['sedmeta-find'],$_REQUEST['sedmeta-replace'],$eText->text,-1,$count);

		  //if str_replace matches anything,
		  //update the return array
		  if($count>0)
		    {
		      $changes[]=array(
				       'item'=>$item['title'],
				       'field'=>$element->name,
				       'old'=>$eText->text,
				       'new'=>$new
				       );
		      if($perform)
			{
			  $html=false;
			  if($new != strip_tags($new))
			    $html = true;
			  $eText->delete();
			  $itemObj->addTextForElement($element,$new,$html);
			}
		      $j++;
		    }


		  break;


		case 'delete':
		  //update the return array
		  
		  $element=$itemObj->getElementById($field['elementID']);
		  $eText = get_record_by_id('ElementText',$field['id']);
		  $new = '';
		  $changes[]=array(
				   'item'=>$item['title'],
				   'field'=>$element->name,
				   'old'=>$eText->text,
				   'new'=>'null'
				   );
		  if($perform)
		    $eText->delete();
		  
		  $j++;
		  break;
	      
		case 'append':
		  if(!isset($_REQUEST['sedmeta-append']))
		    die('ERRORZ! WRONG SET VARS AND JUNK!');//todo error handling
		  if(!isset($_REQUEST['delimiter']))
		    $_REQUEST['delimiter']=' ';
		  $element=$itemObj->getElementById($field['elementID']);
		  $eText = get_record_by_id('ElementText',$field['id']);

		  $new = $eText->text.$_REQUEST['delimiter'].$_REQUEST['sedmeta-append'];
		      
		  $changes[]=array(
				   'item'=>$item['title'],
				   'field'=>$element->name,
				   'old'=>$eText->text,
				   'new'=>$new
				   );

		  if($perform)
		    {
		      $html=false;
		      if($new != strip_tags($new))
			$html = true;
		      $eText->delete();
		      $itemObj->addTextForElement($element,$new,$html);
		    }
		  $j++;
		      
		  
		  break;

		case 'add':
		  if(!isset($_REQUEST['sedmeta-add']))
		    die('ERRORZ! WRONG SET VARS AND JUNK!');//todo error handling
		  $element=$itemObj->getElementById($field['elementID']);
		  if(!in_array($field['elementID'],$made))
		    {
		      $new = $_REQUEST['sedmeta-add'];
		      $changes[]=array(
				       'item'=>$item['title'],
				       'field'=>$element->name,
				       'old'=>'null',
				       'new'=>$new
				       );
		      if($perform)
			{
			  $html=false;
			  if($new != strip_tags($new))
			    $html = true;
			  $itemObj->addTextForElement($element,$new,$html);
			}
		      $j++;
		    }
		      
		  $made[]=$field['elementID'];
		  break;
		} //end switch

	    } //end field item loop
	  
	  $itemObj->saveElementTexts();

	}//end item loop

      if($max>0 && $j>$max)
	{
	  $leftover = count($items)-$i;
	  $j++;
	  if ($leftover>0)
	    {
	      $title='<strong>...and changes for '.$leftover.' more items</strong>   ';
	      if($max<50)
		$title.='<a id="show-more-changes" href="">Show More</a>';
	      $changes[]=array(
			       'item'=>$title,
			       'field'=>'',
			       'old'=>'',
			       'new'=>''
			       );
	    }
	}

      return $changes;
      
    }



    /**
     * Retrieve Items matching selection rules.
     *
     * Retrieve items matching the rules contained in the POST
     * data from the user input form.
     * 
     * @param int $max Maximum number of items to return. If 
     * set to zero, all matching items will be returned.
     *
     * @param array $items Array of items matching the selection
     * rules. Each element of this array is itself an array 
     * containing identifying information for a single matched item.
     */
    private function _getItems($max = 0)
    {

      $rules=array();

      if(isset( $_REQUEST['item-select-meta'] ))
	{
	  for( $i=0; $i < count($_REQUEST['item-rule-elements']); $i++)
	    {

	      $search=urldecode($_REQUEST['item-selectors'][$i]);
	      
	      $neg=false;
	      $exact = true;
	      $case = true;

	      $search = str_replace('*','.*',$search);

	      if(isset($_REQUEST['item-cases'][$i])&& $_REQUEST['item-cases'][$i]=="false")
		{
		  $case = false;
		  $search = strtolower($search);
		}
	      
	      switch($_REQUEST['item-compare-types'][$i])
		{
		case '!exact':
		  $neg = true;
		case 'exact':
		  $search = "/^".$search."$/";
		  break;
		case '!contains':
		  $neg = true;
		case 'contains':
		  $search = '/'.$search.'/';
		  break;
		}

	      $rules[] = array(
			       'field'=>$_REQUEST['item-rule-elements'][$i],
			       'search'=>$search,
			       'case'=>$case,
			       'neg'=>$neg
			       );
	    }
	}

      $params=array();

      //set up query parameters to select items from a given collection
      if( isset($_REQUEST['sedmeta-collection-id']) && $_REQUEST['sedmeta-collection-id'] != 0)
	$params['collection']=$_REQUEST['sedmeta-collection-id'];

      //retrieve all potentially matching items
      $items = get_records("Item",$params,0);

      //if there are any metadata selection rules set
      if(count($rules)>0)
	{
	  $newitems=array();
	  $j=0;
	  
	  //loop through all of the items 
	  foreach($items as $item)
	    {
	      if($max > 0 && ++$j>$max) break;
	     
	      //by default, select all items. 
	      //Each rule can eliminate items 
	      //by setting this variable to false
	      $matched=true; 

	      //loop through the rules
	      foreach($rules as $rule)  
		{
		  //get all elementTexts for this rule's Element 
		  $compareTexts = $item->getElementTextsByRecord($item->getElementById($rule['field'])); 

		  //this variable keeps track of whether any
		  //of the element texts matches the rule
		  $matched2=false;

		  //loop through the element texts
		  foreach ($compareTexts as $compareText)
		    {
		      $comparator = $compareText->text;
		      if(!$rule['case'])
			$comparator = strtolower($compareText->text);
			
		      //perform the search
		      $match = preg_match($rule['search'],$comparator);

		      if ($match===false)
			die('regular expression error!');//TODO proper error handling

		      //negate if necessary
		      if($rule['neg'])
			$match = !(boolean)$match;

		      //throw a flag if we found a match
		      if($match)
			$matched2=true;
		    }

		  //if none of the metadata entries for this field match the rule
		  if(!$matched2) 
		    {
		      //then this item will not be selected
		      $matched=false;

		      //so we decrement our item counter
		      $j--;
		      
		      //and we do not have to check the other rules
		      continue;
		    }
		} //end rule loop

	      //if none of the rules has excluded this item
	      //include it in the updated list of items
	      if($matched)
		$newitems[]=$this->_pullItemData($item);

	    } //end item loop
	} //endif (if there are any rules)
      else //if we had no rules to enforce, and skipped the above loops
	{
	  //generate the return array from all of the items
	  $newitems=array();
	  $j=1;
	  foreach($items as $item)
	    {
	      if($max > 0 && ++$j > $max) break;
	      $newitems[]=$this->_pullItemData($item);       
	    }
	}

      if($j>$max)
	{
	  $leftover = count($items)-$max;
	  if ($leftover>0)
	    {
	      $title = 'plus '.$leftover.' more items. ';
	      if($max<90)
		$title.=' <a id="show-more-items" href="">Show More</a>';
	      $newitems[]=array(
				'title'=>$title,
				'description'=>'',
				'type'=>'',
				'id'=>0
				);
	    }
	}

      return $newitems;
    }

    /**
     *
     *
     */
    private function _pullItemData($item)
    {
      $title = 'untitled';
      $description = 'no description given';
      $typename="undefined";
      $titles = $item->getElementTextsByRecord($item->getElementById(50));
      if(count($titles)>0)
	$title=$titles[0]->text;
      //print_r($titles);
      //die();
      $descriptions=$item->getElementTextsByRecord($item->getElementById(41));
      if(count($descriptions)>0)
	$description=$descriptions[0]->text;
      $type=$item->getItemType();
      if(is_object($type))
	$typename=$type->name;
	   
      $rv = array(
		  'title'=>$title,
		  'description'=>$description,
		  'type'=>$typename,
		  'id'=>$item->id
		  );
      return $rv;

    }
    
    /**
     *
     * Retrieve metadata elements matching selection rules.
     *
     * Retrieve metadata elements from the items provided 
     * which match the rules contained 
     * in the POST data from the user input form.
     * 
     * @param array $items Array of items from whose
     * metadata elements the fields will be selected.
     * 
     * @param int $max Maximum number of items to return. If 
     * set to zero, all matching items will be returned.
     *
     * @param array $elements Array of elements matching the selection
     * rules. Each element of this array is itself an array 
     * containing identifying information for a single matched 
     * metadata element.
     *
     */
    private function _getFields($items,$max=0)
    {
      $fields = array();


      if(!isset($_REQUEST['selectfields']))
	{
	  $fields = $this->_getElementIds();
	} else 
	{
	  $fields = $_REQUEST['selectfields'];
	}

      $newfields = array();

      $j=1;
      $i=0;
      foreach ($items as $item)
	{
	  $i++;
	  if($item['id']==0)
	    break;
	  if($max>0 && $j>$max)
	    break;
	  $itemObj=get_record_by_id('Item',$item['id']);
	  $flag = false;

	  //print_r($fields);
	  //die();

	  foreach($fields as $field)
	    {
	      $element = get_record_by_id('Element',$field);
	      //$element = $itemObj->getElementById($field);
	      $fieldname = $element->name;
	      $elementTexts =  $itemObj->getElementTextsByRecord($element);
	      foreach($elementTexts as $elementText)
		{
		  $newfields[$item['id']][] = array(
						       'field'=>$fieldname,
						       'value'=>$elementText->text,
						       'elementID'=>$element->id,
						       'id'=>$elementText->id
						       );
		  $flag=true;
		}
	    }
	  if($flag)
	    {
	      $newfields[$item['id']]['title']=$item['title'];
	      $j++;
	    }
	  
	}

      if($max > 0 && $j > $max)
	{
	  
	  $leftover = count($items)-$i;
	  if ($leftover>0)
	    {
	      $title = '...and corresponding fields from '.$leftover.' more items.  ';
		if($max<40)
		  $title.='<a id="show-more-items" href="">Show More</a>';
	      $newfields[]=array('title'=>$title);
	    }
	}

       return $newfields;

    }

    /**
     * Validate form input
     *
     * Check hash nonce from the user input form and check
     * user input for illegal characters or suspicious
     * sequences
     *
     * @param void
     * @return void
     */

    private function _validateForm()
    {
      


    }


    
}
