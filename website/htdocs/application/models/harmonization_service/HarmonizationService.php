<?php
/**
 * © French National Forest Inventory 
 * Licensed under EUPL v1.1 (see http://ec.europa.eu/idabc/eupl).
 */ 
require_once APPLICATION_PATH.'/models/abstract_service/AbstractService.php';

/**
 * This is a model allowing to access the harmonization service via HTTP calls.
 * @package models
 */
class Model_HarmonizationService extends Model_AbstractService {

	var $serviceUrl;
	var $logger;

	/**
	 * Class constructor
	 */
	function Model_HarmonizationService() {

		// Initialise the service URL
		$configuration = Zend_Registry::get("configuration");
		$this->serviceUrl = $configuration->harmonizationService_url;

		// Initialise the logger
		$this->logger = Zend_Registry::get("logger");
	}

	/**
	 * Launch the harmonization process
	 *
	 * @param String the country code
	 * @param String the dataset identifier
	 * @return true if the process was OK
	 * @throws Exception if a problem occured on the server side
	 */
	public function harmonizeData($countryCode, $datasetId) {
		$this->logger->debug("harmonizeData : ".$countryCode." ".$requestId);

		$client = new Zend_Http_Client();
		$client->setUri($this->serviceUrl."HarmonizationServlet?action=HarmonizeData");
		$client->setConfig(array(
			'maxredirects' => 0,
			'timeout' => 30));

		$client->setParameterPost('COUNTRY_CODE', $countryCode);
		$client->setParameterPost('DATASET_ID', $datasetId);

		$this->logger->debug("HTTP REQUEST : ".$this->serviceUrl."HarmonizationServlet?action=HarmonizeData");

		$response = $client->request('POST');

		// Check the result status
		if ($response->isError()) {
			$this->logger->debug("Error while harmonizing data : ".$response->getMessage());
			throw new Exception("Error while harmonizing data : ".$response->getMessage());
		}

		// Extract the response body
		$body = $response->getBody();
		$this->logger->debug("HTTP RESPONSE : ".$body);

		// Check the response status
		if (strpos($body, "<Status>OK</Status>") === FALSE) {
			// Parse an error message
			$error = $this->parseErrorMessage($body);
			throw new Exception("Error while harmonizing data : ".$error->errorMessage);
		} else {
			return true;
		}
	}


	/**
	 * Get the status of the harmonisation process.
	 *
	 * @param $datasetId The identifier of the dataset
	 * @param $countryCode The identifier of the country
	 * @param $servletName The name of the servlet to call
	 * @return ProcessStatus the status of the process.
	 * @throws Exception if a problem occured on the server side
	 */
	public function getStatus($datasetId, $countryCode, $servletName) {
		$this->logger->debug("getStatus : ".$datasetId);

		$client = new Zend_Http_Client();
		$client->setUri($this->serviceUrl.$servletName."?action=status");
		$client->setConfig(array(
			'maxredirects' => 0,
			'timeout' => 30));

		$client->setParameterPost('DATASET_ID', $datasetId);
		$client->setParameterPost('COUNTRY_CODE', $countryCode);

		$this->logger->debug("HTTP REQUEST : ".$this->serviceUrl.$servletName."?action=status");

		$response = $client->request('POST');

		// Check the result status
		if ($response->isError()) {
			$this->logger->debug("Error while getting the status : ".$response->getMessage());
			throw new Exception("Error while getting the status : ".$response->getMessage());
		}

		// Extract the response body
		$body = $response->getBody();
		$this->logger->debug("HTTP RESPONSE : ".$body);

		// Check the response status
		if (strpos($body, "<Status>OK</Status>") === FALSE) {
			// Parse an error message
			$error = $this->parseErrorMessage($body);
			throw new Exception("Error while getting the status : ".$error->errorMessage);
		} else {
			return $this->parseStatusResponse($body);
		}
	}
}
