<?php 
/**
 * Sedmeta main admin form
 *
 * This Omeka curator form collects information defining a set of 
 * bulk edits to perform on the omeka database. It includes 
 * functionality to perform the changes, and to return preview 
 * of the affected records.
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

/**
 * Sedmeta main admin form class
 *
 * This Omeka curator form collects information defining a set of 
 * bulk edits to perform on the omeka database. It includes 
 * functionality to perform the changes, and to return preview 
 * of the affected records.
 *
 */

class SedMeta_Form_Main extends Omeka_Form
{
   /**
     * Initialize the form.
     */
    public function init()
    {
        parent::init();

        $this->setAttrib('id', 'sedmeta-form');
        $this->setMethod('post');

	$this->addDisplayGroup(array('', ''), 'sedmeta-items-set');
	$this->addDisplayGroup(array('', ''), 'sedmeta-fields-set');
	$this->addDisplayGroup(array('', ''), 'sedmeta-changes-set');

	
    }


}