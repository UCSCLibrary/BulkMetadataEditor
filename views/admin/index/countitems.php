<?php
/**
 * BulkMetadataEditor count items Ajax callback 
 * 
 * This view encodes in JSON format the number 
 * of items matched by the search parameters
 * and echoes this information to be 
 * processed by browser scripts
 *
 * @copyright Copyright 2014 UCSC Library Digital Initiatives
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GNU GPLv3
 */
echo json_encode($count);
?>