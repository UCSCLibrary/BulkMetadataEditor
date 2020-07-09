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
		_log('[BulkMetadataEditor] ' . __('Process started.'), Zend_Log::INFO);

		$params = $this->_options['params'];

		// TODO Check acl (see ItemsBatchEditAll.php).
		// TODO Add logs by item.

		$view = get_view();

		try {
			$bulkEdit = $view->bulkEdit();
		} catch (Zend_Loader_PluginLoader_Exception $e) {
			$bulkEdit = $this->getViewHelperBulkEdit();
		}

		try {
			$changes = $bulkEdit->perform($params);
			_log('[BulkMetadataEditor] ' . __('Process ended successfully.'), Zend_Log::INFO);
			return $changes;
		} catch (Exception $e) {
			$message = __('An error occurred in background process.');
			_log('[BulkMetadataEditor] ' . $message, Zend_Log::ERR);
			return false;
		}
	}

	/**
	 * Get the view helper bulkEdit.
	 *
	 * @return BulkMetadataEditor_View_Helper_BulkEdit
	 */
	protected function getViewHelperBulkEdit()
	{
		require_once dirname(dirname(dirname(dirname(__FILE__))))
			. DIRECTORY_SEPARATOR . 'views'
			. DIRECTORY_SEPARATOR . 'helpers'
			. DIRECTORY_SEPARATOR . 'BulkEdit.php';
		return new BulkMetadataEditor_View_Helper_BulkEdit();
	}
}