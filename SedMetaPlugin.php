<?php
/**
 * SedMeta Bulk Metadata Search and Replace
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

//require_once dirname(__FILE__) . '/helpers/SedMetaFunctions.php';

/**
 * SedMeta plugin.
 */
class SedMetaPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('install', 'uninstall', 'initialize',
			      'config_form', 'config','define_acl');

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    /**
     * @var array Options and their default values.
     */
    protected $_options = array();

    /**
     * Install the plugin.
     */
    public function hookInstall()
    {
        
    }

    /**
     * Uninstall the plugin.
     */
    public function hookUninstall()
    {        
    }


    /**
     *
     */
    public function hookInitialize()
    {
        
    }

   
    /**
     * Display the plugin config form.
     */
    public function hookConfigForm()
    {
        require dirname(__FILE__) . '/config_form.php';
    }

    /**
     * Set the options from the config form input.
     */
    public function hookConfig()
    {
      if(!isset($_POST['sedmeta-find'])||!isset($_POST['sedmeta-replace']))
	return;
      
      $toFind = $_POST['sedmeta-find'];
      $toReplace = $_POST['sedmeta-replace'];

      //$items = get_records("Item",array());
      $items = array(get_record_by_id("Item",2));
 
      foreach($items as $item)
	{
	  $newElementText=array();
	  $newElementTexts=array();
	  $elementTexts = $item->getAllElementTexts();
	  foreach($elementTexts as $elementText)
	    {
	      $newElementTexts[] = array(
				      'element_id' => $elementText->element_id,
				      'text' => str_replace($toFind,$toReplace,$elementText->text),
				      'html' =>  str_replace($toFind,$toReplace,$elementText->html)
				      );
	    }

	$item->deleteElementTexts();
	$item->addElementTextsByArray($newElementTexts);

	}

	//formatted thusLY:
	//
	//array(
	//    array('element_id' => 1,
         //     'text' => 'foo',
        //      'html' => false)
	//)
	
	$item->__call("saveElementTexts",array());
       	//echo(all_element_texts($item));
       
    }

    /**
     * Define the plugin's access control list.
     */
    public function hookDefineAcl($args)
    {
        $args['acl']->addResource('Sedmeta_Index');
    }

   
    /**
     * Add the SedMeta link to the admin main navigation.
     * 
     * @param array Navigation array.
     * @return array Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('SedMeta'),
            'uri' => url('sed-meta'),
            'resource' => 'Sedmeta_Index',
            'privilege' => 'index'
        );
        return $nav;
    }
    
}
