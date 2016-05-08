<?php
/**
 * BulkMetadataEditor Bulk Metadata Search and Replace
 *
 * This Omeka 2.1+ plugin is intended to expedite the 
 * process of editing metadata in Omeka collections of 
 * digital objects by providing tools for administrators 
 * to edit many items at once based on prespecified rules.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

//require_once dirname(__FILE__) . '/helpers/BulkMetadataEditorFunctions.php';

/**
 * BulkMetadataEditor plugin class.
 *
 * The main class of the BulkMetadataEditor bulk search and replace 
 * plugin for Omeka 2.1+
 */
class BulkMetadataEditorPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array('define_acl','admin_head','initialize');

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array('admin_navigation_main');

    public function hookInitialize()
    {
      add_translation_source(dirname(__FILE__) . '/languages');
    }

    /**
     * Queue css and javascript files when admin section loads
     *
     *@return void
     */
    public function hookAdminHead()
    {
        $language = array(
            'PleaseWait' => __('Please wait...'),
            'Title' => __('Title'),
            'Description' => __('Description'),
            'ItemType' => __('Item Type'),
            'Item' => __('Item'),
            'Field' => __('Field'),
            'OldValue' => __('Old Value'),
            'NewValue' => __('New Value'),
            'ErrorGeneratingPreview' => __('Error generating preview! :('),
            'CouldNotGeneratePreview' => __('Apologies, but we could not generate a preview at this time.')
                . ' ' . __('You may be asking for too many changes at once.')
                . ' ' . __('Anyway, the bulk edition will be done in the background.'),
            'ItemsPreviewRequestTooLong' => __('The items preview request is taking too long!')
                . ' ' . __('You must be trying to select a ton of items at once.')
                . ' ' . __('Preview is not possible, but the bulk edition will be done in the background.'),
            'FieldsPreviewRequestTooLong' => __('The fields preview request is taking too long!')
                . ' ' . __('You must be trying to select a ton of fields at once.')
                . ' ' . __('Preview is not possible, but the bulk edition will be done in the background.'),
            'ChangesPreviewRequestTooLong' => __('The changes preview request is taking too long!')
                . ' ' . __('You must be trying to make a ton of changes at once.')
                . ' ' . __('Preview is not possible, but the bulk edition will be done in the background.'),
            'SelectActionPerform' => __('Please select an action to perform.'),
            'NoItemFound' => __('No matching items found.'),
            'NoFieldFound' => __('No matching field found.'),
            'NoChange' => __('No change or no preview.'),
            'PlusItems' => __('Plus %s more items.', '%s'),
            'PlusFields' => __('...and corresponding fields from a total of %s items.', '%s'),
            'PlusChanges' => __('...and changes for a total of %s items.', '%s'),
            'ShowMore' => __('Show more.'),
        );
        $language = json_encode($language);
        queue_js_string("Omeka.BulkMetadataEditor = {language: $language};");
        queue_js_file('BulkMetadataEditor');
        queue_css_file('BulkMetadataEditor');
    }

    /**
     * Define the plugin's access control list.
     *
     * Add a new resource to the access control list
     * corresponding the the metadata editing page
     *
     *@param array $args Parameters sent to the plugin hook
     *@return void
     */
    public function hookDefineAcl($args)
    {
        $args['acl']->addResource('BulkMetadataEditor_Index');
    }

   
    /**
     * Add the BulkMetadataEditor link to the admin main navigation.
     * 
     * @param array $nav Navigation array.
     * @return array $nav Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Bulk Editor'),
            'uri' => url('bulk-metadata-editor'),
            'resource' => 'BulkMetadataEditor_Index',
            'privilege' => 'index'
        );
        return $nav;
    }
    
}
