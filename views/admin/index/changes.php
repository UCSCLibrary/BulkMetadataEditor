<?php
/**
 * BulkMetadataEditor changes preview Ajax callback 
 * 
 * This view encodes in JSON format all changes 
 * to be performed by form settings, and echoes 
 * this information to be processed by 
 * browser scripts
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
echo json_encode($changes);
?>