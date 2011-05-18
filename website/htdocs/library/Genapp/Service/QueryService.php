<?php
/**
 * © French National Forest Inventory
 * Licensed under EUPL v1.1 (see http://ec.europa.eu/idabc/eupl).
 */

/**
 * The Query Service.
 *
 * This service handles the queries used to feed the query interface with ajax requests.
 *
 * @package classes
 */
class Genapp_Service_QueryService {

	/**
	 * The logger.
	 */
	var $logger;

	/**
	 * The models.
	 */
	var $metadataModel;
	var $genericModel;
	var $resultLocationModel;
	var $predefinedRequestModel;

	/**
	 * The generic service
	 */
	var $genericService;

	/**
	 * The schema.
	 */
	var $schema;

	/**
	 * Constructor.
	 *
	 * @param String $schema the schema
	 */
	function __construct($schema) {

		// Initialise the logger
		$this->logger = Zend_Registry::get("logger");

		// Initialise the metadata models
		$this->metadataModel = new Genapp_Model_DbTable_Metadata_Metadata();
		$this->genericModel = new Genapp_Model_DbTable_Generic_Generic();
		$this->resultLocationModel = new Application_Model_DbTable_Mapping_ResultLocation();
		$this->predefinedRequestModel = new Application_Model_DbTable_Website_PredefinedRequest();

		// The service used to build generic info from the metadata
		$this->genericService = new Genapp_Service_GenericService();

		// Configure the schema
		$this->schema = $schema;
	}

	/**
	 * Generate the JSON structure corresponding to a list of result and criteria columns.
	 *
	 * @param Array[FormFormat] $forms the list of FormFormat elements
	 */
	private function _generateFormsJSON($forms) {

		$json = '{success:true,data:[';

		foreach ($forms as $form) {
			// Add the criteria
			$json .= "{".$form->toJSON().',criteria:[';
			foreach ($form->criteriaList as $field) {
				$json .= '{'.$field->toCriteriaJSON();
				// For the SELECT field, get the list of options
				if ($field->type == "CODE" || $field->type == "ARRAY") {

					if ($field->subtype == "MODE") {
						$options = $this->metadataModel->getModes($field->unit);
						$json .= ',params:{options:[';
						foreach ($options as $code => $label) {
							$json .= '['.json_encode($code).','.json_encode($label).'],';
						}
						$json = substr($json, 0, -1);
						$json .= ']}';
					} else if ($field->subtype == "TREE") {
						// Get the nodes of the tree, from the root ("-1") and down to 2 levels
						//$options = $this->metadataModel->getTreeModes($field->unit, -1, 2);
						$json .= ',params:{options:[';
						//foreach ($options as $code => $label) {
						//	$json .= '["'.$code.'","'.$label.'"],';
						//}
						//$json = substr($json, 0, -1);
						$json .= ']}';
					}
				}
				// For the RANGE field, get the min and max values
				if ($field->type == "NUMERIC" && "RANGE") {
					$range = $this->metadataModel->getRange($field->data);
					$json .= ',params:{min:'.$range->min.',max:'.$range->max.'}';
				}
				$json .= '},';

			}
			if (count($form->criteriaList) > 0) {
				$json = substr($json, 0, -1);
			}
			// Add the columns
			$json .= '],columns:[';
			foreach ($form->resultsList as $field) {
				$json .= '{'.$field->toResultJSON().'},';
			}
			if (count($form->resultsList) > 0) {
				$json = substr($json, 0, -1);
			}
			$json .= ']},';
		}
		if (count($forms) > 0) {
			$json = substr($json, 0, -1);
		}
		$json = $json.']}';

		return $json;
	}

	/**
	 * Get the list of available datasets.
	 *
	 * @return JSON The list of datasets
	 */
	public function getDatasets() {
		$datasetIds = $this->metadataModel->getDatasetsForDisplay();

		$json = "{";
		$json .= "metaData:{";
		$json .= "root:'rows',";
		$json .= "fields:[";
		$json .= "'id',";
		$json .= "'label',";
		$json .= "'is_default'";
		$json .= "]";
		$json .= "},";
		$json .= "rows:".json_encode($datasetIds).'}';

		return $json;
	}

	/**
	 * Get the list of available forms and criterias for the dataset.
	 *
	 * @return JSON.
	 */
	public function getForms($datasetId, $requestName) {
		$this->logger->debug('getforms');

		$this->logger->debug('datasetId : '.$datasetId);
		$this->logger->debug('requestName : '.$requestName);

		// If request name is filled then we are coming from the predefined request screen
		if (!empty($requestName)) {
			$forms = $this->_ajaxgetpredefinedrequest($requestName);
		} else {
			$forms = $this->metadataModel->getForms($datasetId, $this->schema);
			foreach ($forms as $form) {
				// Fill each form with the list of criterias and results
				$form->criteriaList = $this->metadataModel->getFormFields($datasetId, $form->format, $this->schema, 'criteria');
				$form->resultsList = $this->metadataModel->getFormFields($datasetId, $form->format, $this->schema, 'result');
			}
		}

		return $this->_generateFormsJSON($forms);

	}

	/**
	 * Get the description of the columns of the result of the query.
	 *
	 * @param String $datasetId the dataset identifier
	 * @param FormQuery $formQuery the form request object
	 * @param Boolean $withSQL indicate that we want the server to return the genetared SQL
	 * @return JSON
	 */
	public function getResultColumns($datasetId, $formQuery, $withSQL = false) {
		$this->logger->debug('getResultColumns');

		$json = "";

		// Transform the form request object into a table data object
		$queryObject = $this->genericService->getFormQueryToTableData($this->schema, $formQuery);

		//$this->logger->debug('$queryObject : '.print_r($queryObject, true));

		if (sizeof($formQuery->results) == 0) {
			$json = "{ success: false, errorMessage: 'At least one result column should be selected'}";
		} else {

			// Generate the SQL Request
			$select = $this->genericService->generateSQLSelectRequest($this->schema, $queryObject);
			$fromwhere = $this->genericService->generateSQLFromWhereRequest($this->schema, $queryObject);

			$this->logger->debug('$select : '.$select);
			$this->logger->debug('$fromwhere : '.$fromwhere);

			// Clean previously stored results
			$sessionId = session_id();
			$this->logger->debug('SessionId : '.$sessionId);
			$this->resultLocationModel->cleanPreviousResults($sessionId);

			// Identify the field carrying the location information
			$tables = $this->genericService->getAllFormats($this->schema, $queryObject);
			$locationField = $this->metadataModel->getLocationTableFields($this->schema, array_keys($tables));

			// Run the request to store a temporary result table (for the web mapping)
			$this->resultLocationModel->fillLocationResult($fromwhere, $sessionId, $locationField->format, $this->visualisationSRS);

			// Calculate the number of lines of result
			$countResult = $this->genericModel->executeRequest("SELECT COUNT(*) as count ".$fromwhere);

			// TODO : Move this part somewhere else

			// Get the website session
			$websiteSession = new Zend_Session_Namespace('website');
			// Store the metadata in session for subsequent requests
			$websiteSession->resultColumns = $queryObject->editableFields;
			$websiteSession->datasetId = $datasetId;
			$websiteSession->SQLSelect = $select;
			$websiteSession->SQLFromWhere = $fromwhere;
			$websiteSession->queryObject = $queryObject;
			$websiteSession->count = $countResult[0]['count'];
			$websiteSession->locationFormat = $locationField->format;
			$websiteSession->schema = $this->schema;

			// Send the result as a JSON String
			$json = '{success:true,';

			// Metadata
			$json .= '"columns":[';
			// Get the titles of the columns
			foreach ($formQuery->results as $formField) {

				// Get the full description of the form field
				$formField = $this->metadataModel->getFormField($formField->format, $formField->data);

				// Export the JSON
				$json .= '{'.$formField->toJSON().', hidden:false},';
			}
			// Add the identifier of the line
			$json .= '{name:"id",label:"Identifier of the line",inputType:"TEXT",definition:"The plot identifier", hidden:true},';
			// Add the plot location in WKT
			$json .= '{name:"location_centroid",label:"Location centroid",inputType:"TEXT",definition:"The plot location", hidden:true}';
			$json .= ']';
			if ($withSQL) {
				$json .= ', "SQL":'.json_encode($select.$fromwhere);
			}
			$json .= '}';
		}

		return $json;

	}

	/**
	 * Get a page of query result data.
	 *
	 * @param Integer $start the start line number
	 * @param Integer $length the size of a page
	 * @param String $sort the sort column
	 * @param String $sortDir the sort direction (ASC or DESC)
	 * @return JSON
	 */
	public function getResultRows($start, $length, $sort, $sortDir) {
		$this->logger->debug('getResultRows');
		$json = "";

		try {

			// Retrieve the SQL request from the session
			$websiteSession = new Zend_Session_Namespace('website');
			$select = $websiteSession->SQLSelect;
			$fromwhere = $websiteSession->SQLFromWhere;
			$countResult = $websiteSession->count;

			// Retrive the session-stored info
			$resultColumns = $websiteSession->resultColumns; // array of TableField

			$filter = "";
			if ($sort != "") {
				// $sort contains the form format and field
				$split = explode("__", $sort);
				$formField = new Genapp_Model_Metadata_FormField();
				$formField->format = $split[0];
				$formField->data = $split[1];
				$tableField = $this->genericService->getFormToTableMapping($this->schema, $formField);
				$key = $tableField->format.'__'.$tableField->data;
				$filter .= " ORDER BY ".$key." ".$sortDir.", id";
			} else {
				$filter .= " ORDER BY id"; // default sort to ensure consistency
			}
			if (!empty($length)) {
				$filter .= " LIMIT ".$length;
			}
			if (!empty($start)) {
				$filter .= " OFFSET ".$start;
			}

			// Execute the request
			$result = $this->genericModel->executeRequest($select.$fromwhere.$filter);

			// Prepare the needed traductions
			$traductions = array();
			foreach ($resultColumns as $tableField) {
				if ($tableField->type == "CODE") {
					if ($tableField->subtype == "DYNAMIC") {
						$traductions[strtolower($tableField->format.'__'.$tableField->data)] = $this->metadataModel->getDynamodes($tableField->unit);
					} else {
						$traductions[strtolower($tableField->format.'__'.$tableField->data)] = $this->metadataModel->getModes($tableField->unit);
					}
				}
			}

			// Send the result as a JSON String
			$json = '{success:true,';
			$json .= 'total:'.$countResult.',';
			$json .= 'rows:[';
			foreach ($result as $line) {
				$json .= '[';

				foreach ($resultColumns as $tableField) {

					$key = strtolower($tableField->format.'__'.$tableField->data);
					$value = $line[$key];

					if ($tableField->type == "CODE" && $value != "") {
						// Manage code traduction
						$label = isset($traductions[$key][$value]) ? $traductions[$key][$value] : '';
						$json .= json_encode($label == null ? '' : $label).',';
					} else {
						$json .= json_encode($value).',';
					}
				}

				// Add the line id
				$json .= json_encode($line['id']).',';

				// Add the plot location in WKT
				$json .= json_encode($line['location_centroid']); // The last column is the location center

				$json .= '],';
			}
			if (sizeof($result) != 0) {
				$json = substr($json, 0, -1);
			}
			$json .= ']}';
		} catch (Exception $e) {
			$this->logger->err('Error while getting result : '.$e);
			$json = "{success:false,errorMessage:'".json_encode($e->getMessage())."'}";
		}

		return $json;

	}

	/**
	 * Setup the BoundingBox.
	 *
	 * @param Integer $xmin x min position
	 * @param Integer $xmax x max position
	 * @param Integer $ymin y min position
	 * @param Integer $ymax y max position
	 * @return Array the setup BoundingBox
	 */
	private function _setupBoundingBox($xmin, $xmax, $ymin, $ymax, $minSize = 10000) {

		$diffX = $xmax - $xmin;
		$diffY = $ymax - $ymin;

		//Enlarge the bb if it's too small (like for the point)
		if ($diffX < $minSize) {
			$addX = ($minSize - $diffX) / 2;
			$xmin = $xmin - $addX;
			$xmax = $xmax + $addX;
			$diffX = $minSize;
		}
		if ($diffY < $minSize) {
			$addY = ($minSize - $diffY) / 2;
			$ymin = $ymin - $addY;
			$ymax = $ymax + $addY;
			$diffY = $minSize;
		}

		//Setup the bb like a square
		$diffXY = $diffX - $diffY;
		if ($diffXY < 0) {
			//The bb is highter than large
			$xmin = $xmin + $diffXY / 2;
			$xmax = $xmax - $diffXY / 2;
		} else {
			//The bb is larger than highter
			$ymin = $ymin - $diffXY / 2;
			$ymax = $ymax + $diffXY / 2;
		}
		return array(
			'x_min' => $xmin,
			'y_min' => $ymin,
			'x_max' => $xmax,
			'y_max' => $ymax);
	}

	/**
	 * Get the details associed with a result line (clic on the "detail button").
	 *
	 * @param String $id The identifier of the line
	 * @param String $detailLayers The names of the layers used to display the images in the detail panel.
	 * @return JSON representing the detail of the result line.
	 */
	public function getDetails($id, $detailLayers) {

		$this->logger->debug('getDetails : '.$id);

		// Get the base URL
		$baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();

		// Configure the projection systems
		$configuration = Zend_Registry::get("configuration");
		$visualisationSRS = $configuration->srs_visualisation;

		// Transform the identifier in an array
		$keyMap = array();
		$idElems = explode("/", $id);
		$i = 0;
		$count = count($idElems);
		while ($i < $count) {
			$keyMap[$idElems[$i]] = $idElems[$i + 1];
			$i += 2;
		}

		// Prepare a data object to be filled
		$data = $this->genericService->buildDataObject($keyMap["SCHEMA"], $keyMap["FORMAT"], null, true);

		// Complete the primary key info with the session values
		foreach ($data->infoFields as $infoField) {
			if (!empty($keyMap[$infoField->data])) {
				$infoField->value = $keyMap[$infoField->data];
			}
		}

		// Get the detailled data
		$result = $this->genericModel->getDatum($data);

		// The data ancestors
		$ancestors = $this->genericModel->getAncestors($data, true);
		$ancestors = array_reverse($ancestors);

		// Get children too
		$children = $this->genericModel->getChildren($data);

		// Look for a geometry object in order to calculate a bounding box
		// Look for the plot location
		$bb = null;
		$bb2 = null;
		$locationTable = null;
		foreach ($data->getFields() as $field) {
			if ($field->unit == "GEOM") {
				// define a bbox around the location
				$bb = $this->_setupBoundingBox($field->xmin, $field->xmax, $field->ymin, $field->ymax);

				// Prepare an overview bbox
				$bb2 = $this->_setupBoundingBox($field->xmin, $field->xmax, $field->ymin, $field->ymax, 200000);

				$locationTable = $data;
				break;
			}
		}
		if ($bb == null) {
			foreach ($ancestors as $ancestor) {
				foreach ($ancestor->getFields() as $field) {
					if ($field->unit == "GEOM") {
						// define a bbox around the location
						$bb = $this->_setupBoundingBox($field->xmin, $field->xmax, $field->ymin, $field->ymax);

						// Prepare an overview bbox
						$bb2 = $this->_setupBoundingBox($field->xmin, $field->xmax, $field->ymin, $field->ymax, 200000);

						$locationTable = $ancestor;
						break;
					}
				}
			}
		}

		// Defines the panel title and the mapsserver parameters.
		$mapservParams = '';
		$title = '';
		foreach ($locationTable->getInfoFields() as $primaryKey) {
			$mapservParams .= '&'.$primaryKey->columnName.'='.$primaryKey->value;
			if ($title !== '') {
				$title .= '_';
			}
			$title .= $primaryKey->value;
		}

		// Title of the detail message
		$json = "{title:'$title', ";
		$json .= "formats:[";
		// List all the formats, starting with the ancestors
		foreach ($ancestors as $ancestor) {
			$ancestorJSON = $this->genericService->datumToDetailJSON($ancestor);
			if ($ancestorJSON !== '') {
				$json .= $ancestorJSON.',';
			}
		}
		// Add the current data
		$dataJSON = $this->genericService->datumToDetailJSON($data);
		if ($dataJSON !== '') {
			$json .= $dataJSON.',';
		}

		// Add the children
		if (!empty($children)) {
			foreach ($children as $format => $listChild) {
				$childrenJSON = $this->genericService->dataToDetailJSON($listChild);
				if ($childrenJSON !== '') {
					$json .= $childrenJSON.',';
				}
			}
		}

		$json .= "], ";
		$json .= "maps:[{title:'image',";
		$json .= "url:'".$baseUrl."/proxy/gettile?";
		$json .= "&LAYERS=".(empty($detailLayers) ? '' : $detailLayers[0]);
		$json .= "&TRANSPARENT=true";
		$json .= "&FORMAT=image%2FPNG";
		$json .= "&SERVICE=WMS";
		$json .= "&VERSION=1.1.1";
		$json .= "&REQUEST=GetMap";
		$json .= "&STYLES=";
		$json .= "&EXCEPTIONS=application%2Fvnd.ogc.se_inimage";
		$json .= "&SRS=EPSG%3A".$visualisationSRS;
		$json .= "&BBOX=".$bb['x_min'].",".$bb['y_min'].",".$bb['x_max'].",".$bb['y_max'];
		$json .= "&WIDTH=300";
		$json .= "&HEIGHT=300";
		$json .= "&map.scalebar=STATUS+embed";
		$json .= "&sessionid=".session_id();
		$json .= $mapservParams;
		$json .= "'},"; // end of map
		$json .= "{title:'overview',";
		$json .= "url:'".$baseUrl."/proxy/gettile?";
		$json .= "&LAYERS=".(empty($detailLayers) ? '' : $detailLayers[1]);
		$json .= "&TRANSPARENT=true";
		$json .= "&FORMAT=image%2FPNG";
		$json .= "&SERVICE=WMS";
		$json .= "&VERSION=1.1.1";
		$json .= "&REQUEST=GetMap";
		$json .= "&STYLES=";
		$json .= "&EXCEPTIONS=application%2Fvnd.ogc.se_inimage";
		$json .= "&SRS=EPSG%3A".$visualisationSRS;
		$json .= "&BBOX=".$bb2['x_min'].",".$bb2['y_min'].",".$bb2['x_max'].",".$bb2['y_max'];
		$json .= "&WIDTH=300";
		$json .= "&HEIGHT=300";
		$json .= "&sessionid=".session_id();
		$json .= "&CLASS=REDSTAR";
		$json .= "&map.scalebar=STATUS+embed";
		$json .= $mapservParams;
		$json .= "'}"; // end of overview map
		$json .= "]"; // end of maps
		$json .= "}";

		return $json;

	}

	/**
	 * Get the list of available predefined requests.
	 *
	 * @param String $sort The column used as a sort order
	 * @param String $dir The sort direction (ASC or DESC)
	 * @return JSON The list of predefined requests
	 */
	public function getPredefinedRequestList($sort, $dir) {

		// Get the predefined values for the forms
		$predefinedRequestList = $this->predefinedRequestModel->getPredefinedRequestList($this->schema, $dir, $sort);

		// Generate the JSON string
		$total = count($predefinedRequestList);
		$json = '{success:true, total:'.$total.',rows:[';

		foreach ($predefinedRequestList as $predefinedRequest) {
			$json .= $predefinedRequest->toJSON().",";
		}
		if (strlen($json) > 1) {
			$json = substr($json, 0, -1); // remove the last colon
		}

		$json .= ']}';

		return $json;

	}

	/**
	 * Get the criteria of a predefined requests.
	 *
	 * @param String $requestName the request name
	 * @return JSON
	 */
	public function getPredefinedRequestCriteria($requestName) {
		$this->logger->debug('getPredefinedRequestCriteria');

		// Get the predefined values for the forms
		$predefinedRequestCriterias = $this->predefinedRequestModel->getPredefinedRequestCriteria($requestName);

		// Generate the JSON string
		$total = count($predefinedRequestCriterias);
		$json = '{success:true, criteria:[';

		foreach ($predefinedRequestCriterias as $criteria) {

			$json .= '[';
			$json .= $criteria->toJSON();

			// add some specific options
			if ($criteria->type == "CODE") {

				if ($criteria->subtype == "MODE") {

					$options = $this->metadataModel->getModes($criteria->unit);
					$json .= ',{options:[';
					foreach ($options as $code => $label) {
						$json .= '["'.$code.'","'.$label.'"],';
					}
					$json = substr($json, 0, -1);
					$json .= ']}';
				} else if ($criteria->subtype == "DYNAMIC") {

					$options = $this->metadataModel->getDynamodes($criteria->unit);
					$json .= ',{options:[';
					foreach ($options as $code => $label) {
						$json .= '["'.$code.'","'.$label.'"],';
					}
					$json = substr($json, 0, -1);
					$json .= ']}';
				} else if ($criteria->subtype == "TREE") {

					// Get the nodes of the tree, from the root (-1) and down to 2 levels
					$options = $this->metadataModel->getTreeModes($criteria->unit, -1, 2);
					$json .= ',{options:[';
					foreach ($options as $code => $label) {
						$json .= '["'.$code.'","'.$label.'"],';
					}
					$json = substr($json, 0, -1);
					$json .= ']}';
				}
			} else if ($criteria->type == "NUMERIC" && $criteria->subtype == "RANGE") {
				// For the RANGE field, get the min and max values
				$range = $this->metadataModel->getRange($criteria->data);
				$json .= ',{min:'.$range->min.',max:'.$range->max.'}';
			} else {
				$json .= ',{}'; // no options
			}

			$json .= '],';
		}

		if ($total != 0) {
			$json = substr($json, 0, -1);
		}

		$json .= ']}';

		return $json;

	}

}
