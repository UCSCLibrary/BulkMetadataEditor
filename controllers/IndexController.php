<?php
/**
 * BulkMetadataEditor Index Controller class file
 *
 * This controller enforces selection rules on items and metadata 
 * elements, and performs various builk editing operations
 * on the database of element texts associated with Omeka items.
 * This class contains the bulk of the functionality of the
 * BulkMetadataEditor plugin.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */



/**
 * The BulkMetadataEditor index controller class.
 *
 * @package BulkMetadataEditor
 */
class BulkMetadataEditor_IndexController extends Omeka_Controller_AbstractActionController
{    


  /**
   * Process form data and prepare to display the main BulkMetadataEditor form.
   *
   * If the form has been submitted, it performs the relevant changes.
   * Regardless, it populates the arrays of options for the view's
   * dropdown menus.
   *
   * @return void
   */
    public function indexAction()
    {
      $flashMessenger = $this->_helper->FlashMessenger;
      $message = 'The requested changes have been applied to the database';
      $status = 'success';

      $this->view->form_compare_options = $this->_getFormCompareOptions();

      include_once(dirname(dirname(__FILE__))."/forms/Main.php");
      try{
	$this->view->form = new BulkMetadataEditor_Form_Main();
      }catch(Exception $e) {
	$flashMessenger->addMessage('Error loading metadata editing form: '.$e->getMessage(),'error');
      }

      //if the form was submitted
      if ($this->getRequest()->isPost()
	&& $this->view->form->isValid($this->getRequest()->getPost())) {

	try
	  {
	    $this->_perform();

	  } catch (Exception $e) 
	      {
		$message = $e->getMessage();
		$status = 'error';
	      }
	  
	$flashMessenger->addMessage($message,$status);
	  
      }
      
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
      try{
	$this->view->items = $this->_getItems($max);
      }catch(Exception $e) {
	$this->view->items = array(array("Error",$e->getMessage(),"",""));
      }
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
      try{
	$this->view->items = $this->_getItems($max);
	$this->view->count = count($this->_getItems());
      }catch(Exception $e) {
	$this->view->items = array(array("Error",$e->getMessage(),"",""));
      }
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
      try{
	$this->view->items = $this->_getItems($max);
	$this->view->fields = $this->_getFields($this->_getItems(),$max);
      }catch(Exception $e) {
	return(array());
      }
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
      try{
	$this->view->items = $this->_getItems($max);
	$items=$this->_getItems();
	$fields=$this->_getFields($items);
	$this->view->changes = $this->_getChanges($items,$fields,$max,false);
      }catch(Exception $e) {
	$this->view->items = array(array("Error",$e->getMessage(),"",""));
      }
    }



    /**
     * Perform the edits specified by the form input.
     *
     * This function calls the matching subroutines
+     * with no maximum number of results, and 
     * alerts the changes subroutine to perform the 
     * changes rather than just displaying them. 
     */
    private function _perform()
    {
      $max = 0;
      try{
	$this->view->items = $this->_getItems($max);
	$items=$this->_getItems();
	$fields=$this->_getFields($items);
	$this->_getChanges($items,$fields,$max,true);
      }catch(Exception $e) {
	throw $e;
      }
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
      if(!isset($_REQUEST['changesRadio']))
	throw new Exception("Please select an action to perform");
      $changes=array();

      $j=1;
      $i=0;

      foreach($items as $item)
	{
	  $i++;
	  if($item['id']==0)
	    break;
	  $made=array();
	  if(empty($item))
	    continue;
	  if(!isset($fields[$item['id']]) && $_REQUEST['changesRadio']!='add')
	    continue;

	  $itemObj=get_record_by_id('Item',$item['id']);
	  
	  if($_REQUEST['changesRadio']=='add')
	    {
	      $fieldItem = array();

	      if(!isset($_REQUEST['selectFields']))
		{
		  $fields = $this->_getElementIds();
		} else 
		{
		  $fields = $_REQUEST['selectFields'];
		}
	      foreach($fields as $elementID)
		{
		  $fieldItem[]=array('elementID'=>$elementID);
		}
	    } else
	    {
	      $fieldItem = $fields[$item['id']];
	      unset($fieldItem['title']);
	    }

	  if($max>0 and $j>$max) break;
	  foreach($fieldItem as $field)
	    {
	      $replaceType="normal";
	      switch($_REQUEST['changesRadio'])
		{
		case 'preg':
		  $replaceType="preg";

		case 'replace':

		  //expect a 'find' and 'replace' variable
		  if(!isset($_REQUEST['bmeSearch'])||!isset($_REQUEST['bmeReplace']))
		    throw new Exception("Please define search and replace terms");//TODO:proper error handling

		  $element=$itemObj->getElementById($field['elementID']);
		  //$eText = $itemObj->getElementTextsByRecord($element);
		  $eText = get_record_by_id('ElementText',$field['id']);
		      
		  $count=0;

		  if($replaceType=="normal")
		    $new=str_replace($_REQUEST['bmeSearch'],$_REQUEST['bmeReplace'],$eText->text,$count);
		  elseif($replaceType=="regexp")
		    $new=preg_replace($_REQUEST['bmeSearch'],$_REQUEST['bmeReplace'],$eText->text,-1,$count);

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
			  try {
			    $html=false;
			    if($new != strip_tags($new))
			      $html = true;
			    $eText->delete();
			    $itemObj->addTextForElement($element,$new,$html);
			  }catch (Exception $e) {
			    throw $e;
			  }
			}
		      $j++;
		    }


		  break;


		case 'delete':
		  //update the return array
		  try{
		    $element=$itemObj->getElementById($field['elementID']);
		    $eText = get_record_by_id('ElementText',$field['id']);
		  }catch(Exception $e) {
		    throw $e;
		  }

		  if(empty($item['title']) || empty($element->name) || empty($eText->text) ) {
		    throw new Exception("Error retrieving item data for deletion.");
		  }

		  $new = '';
		  $changes[]=array(
				   'item'=>$item['title'],
				   'field'=>$element->name,
				   'old'=>$eText->text,
				   'new'=>'null'
				   );
		  if($perform) {
		    try{
		      $eText->delete();
		    }catch (Exception $e) {
		      throw $e;
		    }
		  }

		  $j++;
		  break;
	      
		case 'append':
		  if(!isset($_REQUEST['bmeAppend']))
		    throw new Exception("Please input some text to append");

		  if(!isset($_REQUEST['delimiter']))
		    $_REQUEST['delimiter']=' ';

		  try{
		    $element=$itemObj->getElementById($field['elementID']);
		    $eText = get_record_by_id('ElementText',$field['id']);

		    $new = $eText->text.$_REQUEST['delimiter'].$_REQUEST['bmeAppend'];

		  }catch (Exception $e) {
		    throw $e;
		  }
		      
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

		      try{
			$eText->delete();
			$itemObj->addTextForElement($element,$new,$html);
		      } catch(Exception $e) {
			throw $e;
		      }
		    }
		  $j++;
		     
		  break;

		case 'add':
		  if(!isset($_REQUEST['bmeAdd']))
		    throw new Exception('Please input some text to add.');

		  try{
		    $element=$itemObj->getElementById($field['elementID']);
		  } catch(Exception $e) {
		    throw $e;
		  }

		  if(!in_array($field['elementID'],$made))
		    {
		      $new = $_REQUEST['bmeAdd'];
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
			  try {
			    $itemObj->addTextForElement($element,$new,$html);
			  } catch(Exception $e) {
			    throw $e;
			  }
			}
		      $j++;
		    }
		  $made[]=$field['elementID'];
		  break;
		} //end switch

	    } //end field item loop
	  try {
	    $itemObj->saveElementTexts();
	  } catch(Exception $e) {
	    throw $e;
	  }

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

      if(!empty($_REQUEST['itemSelectMeta']))
	{
	  for( $i=0; $i < count($_REQUEST['item-rule-elements']); $i++)
	    {

	      $search=urldecode($_REQUEST['item-selectors'][$i]);

	      $neg=false;
	      $exact = true;
	      $case = true;

	      $search = preg_quote($search);
	      $search = str_replace('\*','.*',$search);

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
      if( isset($_REQUEST['bmeCollectionId']) && $_REQUEST['bmeCollectionId'] != 0)
	$params['collection']=$_REQUEST['bmeCollectionId'];

      //retrieve all potentially matching items
      try{
	$items = get_records("Item",$params,0);
      } catch(Exception $e) {
	throw $e;
      }

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
		  try {
		    $compareTexts = $item->getElementTextsByRecord($item->getElementById($rule['field'])); 
		  } catch(Exception $e) {
		    throw $e;
		  }

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
			throw new Exception('Unable to parse regular expression');//TODO proper error handling

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
	      if($matched) {
		try{
		  $newitems[]=$this->_pullItemData($item);   
		} catch(Exception $e) {
		  throw $e;
		}   
	      }

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
	      try {
		$newitems[]=$this->_pullItemData($item);        
	      } catch(Exception $e) {
		throw $e;
	      }
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

      if(count($newitems)==0)
	$newitems = array(array(
				'title'=>'No matching items found',
				'description'=>'',
				'type'=>'',
				'id'=>''
				));

      return $newitems;
    }

    /**
     *Retrieves basic data about an Omeka item
     *
     *@param Object $item Omeka item record object to pull data from
     *@return array $itemData The title, description, type and ID 
     *of the given item as an associative array
     */
    private function _pullItemData($item)
    {
      if(! $item instanceOf Item)
	throw new Exception("Cannot pull item data from a non-item");
      $title = 'untitled';
      $description = 'no description given';
      $typename="undefined";
      $titles = $item->getElementTexts('Dublin Core','Title');
      if(count($titles)>0)
	$title=$titles[0]->text;
      $descriptions = $item->getElementTexts('Dublin Core','Description');
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
      $newfields = array();

      if(!isset($_REQUEST['selectFields']))
	{
	  $fields = $this->_getElementIds();
	} else 
	{
	  $fields = $_REQUEST['selectFields'];
	}

      $j=1;
      $i=0;
      foreach ($items as $item)
	{
	  $i++;
	  if($item['id']==0)
	    break;
	  if($max>0 && $j>$max)
	    break;

	  try {
	    $itemObj=get_record_by_id('Item',$item['id']);
	  }catch(Exception $e) {
	    throw $e;
	  }
	  $flag = false;

	  foreach($fields as $field)
	    {
	      try{
		$element = get_record_by_id('Element',$field);
		$fieldname = $element->name;
		$elementTexts =  $itemObj->getElementTextsByRecord($element);
	      }catch(Exception $e){
		throw $e;
	      }
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
		  $title.='<a id="show-more-fields" href="">Show More</a>';
	      $newfields[]=array('title'=>$title);
	    }
	}

       return $newfields;

    }

    
}
