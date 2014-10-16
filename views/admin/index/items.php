<?php
/**
 * BulkMetadataEditor selected item preview Ajax callback 
 * 
 * This view encodes the items matched by the
 * search criteria in JSON format to be 
 * processed by browser scripts
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */

echo json_encode($items);
?>