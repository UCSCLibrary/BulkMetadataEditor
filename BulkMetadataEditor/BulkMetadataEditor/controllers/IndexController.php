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

    protected $bulkEdit;

    public function init()
    {
        $this->_bulkEdit = $this->view->bulkEdit();
    }

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
        $form = new BulkMetadataEditor_Form_Main();
        $this->view->form = apply_filters('bulk_metadata_editor_form', $form);

        // Check if the form was submitted.
        if ($this->getRequest()->isPost()
                && $this->view->form->isValid($this->getRequest()->getPost())
            ) {
            $this->_launchProcess();
        }
    }

    /**
     * Helper to launch the bulk edition.
     */
    protected function _launchProcess()
    {
        $params = $this->getAllParams();
        $jobDispatcher = Zend_Registry::get('job_dispatcher');
        $options = array('params' => $params);
		
		if (!isset($params['selectFields'])) {
			$message = __('Bulk Metadata edition cannot be started because no field is selected for editing.');
			$status = 'error';
		} elseif (!isset($params['changesRadio'])) {
			$message = __('Bulk Metadata edition cannot be started because no editing type is selected.');
			$status = 'error';
		} elseif (empty($params['useBackgroundJob'])) {
            try {
                $changes = $jobDispatcher->send('BulkMetadataEditor_Job_Process', $options);
                $message = __('Bulk Metadata edition has been processed.');
                $status = 'success';
            } catch (Exception $e) {
                $message = __('Bulk Metadata edition cannot be started: %s', $e->getMessage());
                $status = 'error';
                _log('[BulkMetadataEditor] ' . $message, Zend_Log::ERR);
            }
        } else {
            try {
                $jobDispatcher->setQueueName(BulkMetadataEditor_Job_Process::QUEUE_NAME);
                $jobDispatcher->sendLongRunning('BulkMetadataEditor_Job_Process', $options);
                $message = __('The requested changes are applied to the database one by one in the background.')
                    . ' ' . __('Check logs for success and errors.');
                $status = 'info';
            } catch (Exception $e) {
                $message = __('Bulk Metadata edition cannot be started: %s', $e->getMessage());
                $status = 'error';
            }
        }

        $this->_helper->FlashMessenger->addMessage($message, $status);
    }

    ///// AJAX ACTIONS /////

    /**
     * Retrieves selected items for preview.
     *
     * Retrieves the first few items matching the selection rules and sends them
     * to the view to be served to a browser preview script.
     *
     * @param void
     * @return void
     */
    public function itemsAction()
    {
        $params = $_REQUEST;
        $max = $this->_getParam('max');
        unset($params['max']);
        try {
            $items = $this->_bulkEdit->getItems($params, $max);
            $total = $this->_bulkEdit->countItems($params);
            $this->_helper->json(array(
                'items' => $items,
                'total' => $total,
            ));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json($e->getMessage());
        }
    }

    /**
     * Retrieves the selected metadata elements for preview.
     *
     * Retrieves the number of items matching the selection rules and sends them
     * to the view to be served to a browser preview script.
     *
     * @param void
     * @return void
     */
    public function fieldsAction()
    {
        $params = $_REQUEST;
        $max = $this->_getParam('max');
        unset($params['max']);
        try {
            $items = $this->_bulkEdit->getItems($params);
            $fields = $this->_bulkEdit->getFields($params, $items, $max);
            $this->_helper->json(array(
                'fields' => $fields,
                'total' => count($items),
            ));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json($e->getMessage());
        }
    }

    /**
     * Retrieves the first few edits defined by the form input.
     *
     * Retrieves the first few changes defined by the form input and sends them
     * to the view to be served to a browser preview script.
     *
     * @param void
     * @return void
     */
    public function changesAction()
    {
        $params = $_REQUEST;
        $max = $this->_getParam('max');
        unset($params['max']);
        try {
            $items = $this->_bulkEdit->getItems($params);
            $changes = $this->_bulkEdit->getChanges($params, $max);
            $this->_helper->json(array(
                'changes' => $changes,
                'total' => count($items),
            ));
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $this->_helper->json($e->getMessage());
        }
    }

    ///// END AJAX ACTIONS /////
}