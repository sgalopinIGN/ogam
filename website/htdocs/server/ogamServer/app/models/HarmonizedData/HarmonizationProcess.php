<?php

/**
 * Licensed under EUPL v1.1 (see http://ec.europa.eu/idabc/eupl).
 *
 * © European Union, 2008-2012
 *
 * Reuse is authorised, provided the source is acknowledged. The reuse policy of the European Commission is implemented by a Decision of 12 December 2011.
 *
 * The general principle of reuse can be subject to conditions which may be specified in individual copyright notices.
 * Therefore users are advised to refer to the copyright notices of the individual websites maintained under Europa and of the individual documents.
 * Reuse is not applicable to documents subject to intellectual property rights of third parties.
 */

/**
 * This is a model allowing access to the harmonization process information.
 *
 * @package Application_Model
 * @subpackage HarmonizedData
 */
class Application_Model_HarmonizedData_HarmonizationProcess {

	/**
	 * The logger.
	 *
	 * @var Zend_Log
	 */
	var $logger;

	/**
	 * The models.
	 */
	var $metadataModel;

	/**
	 * The database connection
	 *
	 * @var Zend_Db
	 */
	var $db;

	/**
	 * Initialisation.
	 */
	public function __construct() {

		// Initialise the logger
		$this->logger = Zend_Registry::get("logger");

		// Initialise the metadata model
		$this->metadataModel = new Application_Model_Metadata_Metadata();

		// The database connection
		$this->db = Zend_Registry::get('harmonized_db');
	}

	/**
	 * Destuction.
	 */
	function __destruct() {
		$this->db->closeConnection();
	}

	/**
	 * Get the status of the last harmonization process for a given provider and dataset.
	 *
	 * @param Submission $activeSubmission
	 *        	a submission
	 * @return HarmonizationProcess The completed process info
	 */
	public function getHarmonizationProcessInfo($activeSubmission) {
		$req = " SELECT * ";
		$req .= " FROM harmonization_process ";
		$req .= " LEFT JOIN harmonization_process_submissions USING (harmonization_process_id) ";
		$req .= " WHERE provider_id = ? ";
		$req .= " AND  dataset_id = ? ";
		$req .= " ORDER BY harmonization_process_id DESC LIMIT 1";

		$select = $this->db->prepare($req);
		$select->execute(array(
			$activeSubmission->providerId,
			$activeSubmission->datasetId
		));

		Zend_Registry::get("logger")->info('getHarmonizationProcessInfo : ' . $req);

		$result = $select->fetch();

		$harmonizationProcess = new Application_Object_HarmonizedData_HarmonizationProcess();
		$harmonizationProcess->providerId = $activeSubmission->providerId;
		$harmonizationProcess->datasetId = $activeSubmission->datasetId;
		if (!empty($result)) {
			$harmonizationProcess->harmonizationId = $result['harmonization_process_id'];
			$harmonizationProcess->status = $result['harmonization_status'];
			$harmonizationProcess->date = $result['_creationdt'];
		} else {
			$harmonizationProcess->status = 'UNDONE';
		}

		// Get the label of the dataset
		$dataset = $this->metadataModel->getDataset($harmonizationProcess->datasetId);
		$harmonizationProcess->datasetLabel = $dataset->label;

		return $harmonizationProcess;
	}

	/**
	 * Get the raw_data submissions used by a harmonization process.
	 *
	 * @param HarmonizationProcess $harmonizationProcess
	 *        	the process to complete
	 * @return HarmonizationProcess The completed process info
	 */
	public function getHarmonizationProcessSources($harmonizationProcess) {
		$req = " SELECT * ";
		$req .= " FROM harmonization_process_submissions ";
		$req .= " WHERE harmonization_process_id = ? ";

		$select = $this->db->prepare($req);
		$select->execute(array(
			$harmonizationProcess->harmonizationId
		));

		Zend_Registry::get("logger")->info('getHarmonizationProcessSources : ' . $req);

		foreach ($select->fetchAll() as $row) {
			$harmonizationProcess->submissionIDs[] = $row['raw_data_submission_id'];
		}

		return $harmonizationProcess;
	}
}
