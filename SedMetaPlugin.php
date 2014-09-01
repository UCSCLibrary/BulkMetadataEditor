<?php
/**
 * SedMeta Bulk Metadata Search and Replace
 *
 * This Omeka 2.1+ plugin is intended to expedite the process of editing
 * metadata in Omeka collections of digital objects by providing tools for
 * administrators to edit many items at once based on prespecified rules.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

// require_once dirname(__FILE__) . '/helpers/SedMetaFunctions.php';

/**
 * SedMeta plugin class.
 * The main class of the SedMeta bulk search and replace plugin for Omeka 2.1+
 *
 * @package Omeka\Plugins\SedMeta
 */
class SedMetaPlugin extends Omeka_Plugin_AbstractPlugin
{
    /**
     * @var array Hooks for the plugin.
     */
    protected $_hooks = array(
        'define_acl',
        'admin_head',
    );

    /**
     * @var array Filters for the plugin.
     */
    protected $_filters = array(
        'admin_navigation_main',
    );

    /**
     * Queue css and javascript files when admin section loads
     *
     * @return void
     */
    public function hookAdminHead()
    {
        queue_js_file('SedMeta');
        queue_css_file('SedMeta');
    }

    /**
     * Define the plugin's access control list.
     *
     * Add a new resource to the access control list corresponding the the
     * metadata editing page
     *
     * @param array $args Parameters sent to the plugin hook
     * @return void
     */
    public function hookDefineAcl($args)
    {
        $args['acl'] -> addResource('Sedmeta_Index');
    }

    /**
     * Add the SedMeta link to the admin main navigation.
     *
     * @param array $nav Navigation array.
     * @return array $nav Filtered navigation array.
     */
    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Bulk Editor'),
            'uri' => url('sed-meta'),
            'resource' => 'Sedmeta_Index',
            'privilege' => 'index',
        );
        return $nav;
    }
}
