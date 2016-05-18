<?php
/**
 * BulkMetadataEditor_Job_Process class
 *
 * @package BulkMetadataEditor
 */
class BulkMetadataEditor_Job_Process extends Omeka_Job_AbstractJob
{
    const QUEUE_NAME = 'bulk_metadata_editor_update';

    /**
     * Performs the form.
     */
    public function perform()
    {
        $params = $this->_options['params'];

        // TODO Check acl (see ItemsBatchEditAll.php).
        // TODO Add logs by item.

        $view = get_view();
        try {
            $view->bulkEdit()->perform($params);
        } catch (Exception $e) {
            $message = __('An error occurred in background process.');
            _log(__('Bulk Metadata Editor: %s', $message), Zend_Log::ERR);
            return false;
        }
    }
}
