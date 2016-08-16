<?php

namespace OGAMBundle\Controller;

use Doctrine\ORM\EntityManager;
use OGAMBundle\Entity\Metadata\Dataset;
use OGAMBundle\Entity\RawData\Submission;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;

/**
 * @Route("/integration")
 */
class IntegrationController extends Controller
{
	/**
	 * get the underline entity manager related with
	 * @return EntityManager
	 */
	function getEntityManger(){
		return $this->get('doctrine.orm.raw_data_entity_manager');
	}

	function getLogger(){
		return $this->get('logger');
	}

	/**
	 * Default action.
	 *
     * @Route("/", name = "integration_home")
	 */
	public function indexAction() {
		// Display the default
		return $this->showDataSubmissionPageAction();
	}

    /**
     * Show the data submission page.
     *
     * @Route("/show-data-submission-page")
     */
    public function showDataSubmissionPageAction()
    {
    	$submissions = $this->getEntityManger()->getRepository('OGAMBundle:RawData\Submission')->getAciveSubmissions();

        return $this->render('OGAMBundle:Integration:show_data_submission_page.html.twig', array(
            // ...
            'submissions' => $submissions
        ));
    }

    /**
     * Show the create data submission page.
     *
     * @Route("/show-create-data-submission", name="integration_creation")
     */
    public function showCreateDataSubmissionAction(Request $request)
    {
    	$availaibledData = $this->getDoctrine()->getRepository('OGAMBundle:Metadata\Dataset','metadata')->getDatasetsForUpload();

        return $this->render('OGAMBundle:Integration:show_create_data_submission.html.twig', array(
            'datasets' => $availaibledData,
        	'form' => $this->getDataSubmissionForm()->createView(),
        ));
    }

    /**
     * Build and return the data submission form.
     */
	protected function getDataSubmissionForm() {
		return $this
		->get('form.factory')
  		->createNamedBuilder('data-submission-form')
		->setAction($this->generateUrl('integration_validate_creation'))
		//->setAttribute('name', 'data-submission-form')
		->add('DATASET_ID', ChoiceType::class, array(
			'label' => 'Dataset',
			'required' =>true,
		    'choice_value' => 'id',
			'choice_label' => 'label',
            'choices' => $this->getDoctrine()->getRepository('OGAMBundle:Metadata\Dataset','metadata')->getDatasetsForUpload()
				))
		->add('submit', SubmitType::class)
		->getForm();
	}
    /**
     * Show the upload data page.
     *
     * @Route("/show-upload-data/{id}")
     */
    public function showUploadDataAction(Request $request, Submission $submission)
    {
    	$configuration =  $this->get('ogam.configuration_manager');

    	$showDetail = $configuration->getConfig('showUploadFileDetail', true) == 1;
    	$showModel	= $configuration->getConfig('showUploadFileModel', true) == 1;

    	$dataset = $submission->getDataset();

    	$this->get('logger')->debug('$showDetail : ' . $showDetail);
    	$this->get('logger')->debug('$showModel : ' . $showModel);

        return $this->render('OGAMBundle:Integration:show_upload_data.html.twig', array(
            'dataset' => $dataset,
        	'form'    => $this->getDataUploadForm($submission, $showDetail, $showModel)->createView(),
        	'showModel' => $showModel,
        	'showDetail' => $showDetail
        ));
    }

    /**
     * @Route("/validate-create-data-submission", name="integration_validate_creation")
     */
    public function validateCreateDataSubmissionAction(Request $request)
    {

    	$form = $this->getDataSubmissionForm();
    	$form->handleRequest($request);

    	// Check the validity of the POST
    	if (!$form->isSubmitted() || !$request->isMethod(Request::METHOD_POST)) {
    		$this->get('logger')->debug('form is not a post');
    		$this->redirectToRoute('integration_home');
    	}

    	// Check the validity of the Form
    	if (!$form->isValid()) {
    		$this->get('logger')->debug('form is not valid');
    		return $this->showCreateDataSubmissionAction($request);
    	}

    	$values = $form->getNormData();
    	$dataset = $values['DATASET_ID'];

    	$userLogin  = $this->getUser()->getLogin();
    	$providerId = $this->getUser()->getProvider()->getId();

    	$this->get('logger')->debug('userLogin : ' . $userLogin);
    	$this->get('logger')->debug('providerId : ' . $providerId);

		$submissionId = $this->get('ogam.integration_service')->newDataSubmission($providerId, $dataset->getId(), $userLogin);
    	$submission = $this->getEntityManger()->getReference('OGAMBundle:RawData\Submission', $submissionId);
        return $this->showUploadDataAction($request, $submission);
    }
    /**
     * Build and return the pdata upload form.
     *
     * @param bool $showDetail
     *        	show the list of expected fields in the form (description)
     * @param bool $model
     *        	link to a CSV model file
     * @return Form
     * @throws Exception
     */
    protected function getDataUploadForm(Submission $submission, $showDetail = false, $model = false) {
    	$fileMaxSize = intval($this->get('ogam.configuration_manager')->getConfig('fileMaxSize', '40')); // MBi

    	$formBuilder = $this
		->get('form.factory')
  		->createNamedBuilder('data_upload_form', FormType::class)
		->setAction($this->generateUrl('integration_validate_upload',array('id'=>$submission->getId())));

    	// Get the submission object from the database

    	$requestedFiles = $submission->getDataset()->getFiles();
    	//
    	// For each requested file, add a file upload element
    	//
    	foreach ($requestedFiles as $requestedFile) {
    		$fileelement = $formBuilder->create(
    				$requestedFile->getFormat(),
    				FileType::class,
    				array(
    						'label'       => $this->get('translator')->trans($requestedFile->getLabel() . ': '),
    						'block_name'  => 'file_format',//TODO ?not work form name (with dash) invalid for twig block ...
    						'constraints' => array(new File(array('maxSize'=> "${fileMaxSize}Mi")))
    				)
    		);

    		$formBuilder->add($fileelement);
    	}

    	$formBuilder->add('submit', SubmitType::class);

    	return $formBuilder->getForm();
    }

    /**
     * @Route("/validate-upload-data/{id}", name="integration_validate_upload")
     */
    public function validateUploadDataAction(Request $request, Submission $submission)
    {
    	$this->getLogger()->debug('validateUploadDataAction');

    	$form = $this->getDataUploadForm($submission);
    	$form->handleRequest($request);

    	// Check the validity of the POST
    	if (!$form->isSubmitted() || !$request->isMethod(Request::METHOD_POST)) {
    		$this->get('logger')->debug('form is not a post');
    		return $this->redirectToRoute('integration_home');
    	}

    	// Check the validity of the Form
    	if (!$form->isValid()) {
    		$this->get('logger')->debug('form is not valid');
    		return $this->showUploadDataAction($request, $submission);
    	}

    	// Get the configuration info
    	$configuration = $this->get('ogam.configuration_manager');
    	$uploadDir = $configuration->getConfig('uploadDir', '/var/www/html/upload');

    	//
    	// For each requested file
    	//

    	$requestedFiles = $submission->getDataset()->getFiles();

    	foreach ($requestedFiles as $key => $requestedFile) {
    		$file = $form[$requestedFile->getFormat()]->getData();
    		// Get the uploaded filename
    		$filename = $file->getClientOriginalName();

    		// Print it only if it is not an array (ie: nothing has been selected by the user)
    		if (!is_array($filename)) {
    			$this->getLogger()->debug('uploaded filename ' . $filename);
    		}

    		// Check that the file is present
    		if (empty($file) || !$file->isValid()) {
    			$this->getLogger()->debug('File ' . $requestedFile->format . ' is missing, skipping');
    			unset($requestedFiles[$key]);
    		} else {
    			// Move the file to the upload directory on the php server
    			$this->getLogger()->debug('move file : ' . $filename);
    			$targetPath = $uploadDir . DIRECTORY_SEPARATOR . $submission->getId() . DIRECTORY_SEPARATOR . $requestedFile->getFileType();
    			$targetName = $targetPath . DIRECTORY_SEPARATOR . $filename;
    			@mkdir($uploadDir . DIRECTORY_SEPARATOR . $submission->getId()); // create the submission dir
    			@mkdir($targetPath);

    			$file->move($targetPath, $filename);

    			$this->getLogger()->debug('renamed to ' . $targetName);
    			$requestedFile->filePath = $targetName; //TODO : clean this fake filePath property
    		}
    	}

    	// Send the files to the integration server
    	try {
    		$providerId = $this->getUser()->getProvider()->getId();
    		$this->get('ogam.integration_service')->uploadData($submission->getId(), $providerId , $requestedFiles);
    	} catch (Exception $e) {
    		$this->get('logger')->err('Error during upload: ' . $e);
    		return $this->render('OGAMBundle:Integration:show-data-error.html.twig', array(
    				'error' => $e->getMessage()
    		));
    	}

    	// Redirect the user to the show plot location page
    	// This ensure that the user will not resubmit the data by doing a refresh on the page
    	return $this->redirectToRoute('integration_home');
    }

    /**
     * @Route("/cancel-data-submission", name="integration_cancel")
     */
    public function cancelDataSubmissionAction(Request $request)
    {
		$this->get('logger')->debug('cancelDataSubmissionAction');

		// Desactivate the timeout
		set_time_limit(0);

		// Get the submission Id
		$submissionId = $request->get("submissionId");

		// Send the cancel request to the integration server
		try {
			$this->get('ogam.integration_service')->cancelDataSubmission($submissionId);
		} catch (Exception $e) {
			$this->get('logger')->err('Error during upload: ' . $e);

			return $this->render('OGAMBundle:Integration:data_error.html.twig', array(
					'error' => $e->getMessage()
			));
		}

		// Forward the user to the next step
		return $this->redirectToRoute('integration_home');
    }

    /**
     * @Route("/check-submission", name="integration_check")
     */
    public function checkSubmissionAction(Request $request)
    {
    	$this->getLogger()->debug('checkSubmissionAction');

    	// Get the submission Id
    	$submissionId = $request->get("submissionId");

    	// Send the cancel request to the integration server
    	try {
    		$this->get('ogam.integration_service')->checkDataSubmission($submissionId);
    	} catch (Exception $e) {
    		$this->getLogger()->err('Error during upload: ' . $e);

    		return $this->render('OGAMBundle:Integration:show-data-error', array(
    				'error' => $e->getMessage()
    		));
    	}

    	// Forward the user to the next step
    	//$submission = $this->getEntityManger()->find('OGAMBundle:RawData\Submission', $submissionId);
    	return $this->redirectToRoute('integration_home');
    }

    /**
     * Validate the data.
     * @Route("/validate-data",name="integration_validate")
     * @return Response
     */
    public function validateDataAction(Request $request) {
    	$this->getLogger()->debug('validateDataAction');

    	// Get the submission Id
    	$submissionId = $request->get("submissionId");

    	// Send the cancel request to the integration server
    	try {
    		$this->get('ogam.integration_service')->validateDataSubmission($submissionId);
    	} catch (Exception $e) {
    		$this->getLogger()->err('Error during upload: ' . $e);

    		return $this->render('OGAMBundle:Integration:show_data_error.html.twig', array(
    				'error' => $e->getMessage()
    		));
    	}

    	// Forward the user to the next step
    	return $this->redirectToRoute('integration_home');
    }

    /**
     * Gets the integration status.
     *
     * @param String $servletName
     *        	the name of the servlet
     * @return JSON the status of the process
     */
    protected function getStatus($servletName) {
    	$this->getLogger()->debug('getStatusAction');

    	// Send the cancel request to the integration server
    	try {

    		$submissionId = $this->get('request_stack')->getCurrentRequest()->get("submissionId");

    		$status = $this->get('ogam.integration_service')->getStatus($submissionId, $servletName);
    		$data = array(
    					'success'=> TRUE,
    					'status'=>$status->status
    			);
    		// Echo the result as a JSON
    		if ($status->status === "OK") {
    			return $this->json($data);
    		} else {
    			$data['taskName']= $status->taskName;
    			if ($status->currentCount != null) {
    				$data["currentCount"]= $status->currentCount;
    			}
    			if ($status->totalCount != null) {
    				$data['totalCount'] = $status->totalCount;
    			}
    			return $this->json($data);
    		}
    	} catch (Exception $e) {
    		$this->getLogger()->err('Error during get: ' . $e);

    		return $this->json(array(
    				'success'=> FALSE,
    				"errorMsg"=>  $e->getMessage()

    		));
    	}
    }

    /**
     * Gets the data integration status.
     * @Route("/get-data-status", name="integration_status")
     */
    public function getDataStatusAction()
    {
    	return $this->getStatus('DataServlet');
    }

    /**
     * Gets the check status.
     * @Route("/check-data-status", name="integration_checkstatus")
     */
    public function getCheckStatusAction()
    {
    	return $this->getStatus('CheckServlet');
    }

    /**
     * Generate a CSV file, model for import files,
	 * with as first line (commented), the names of the expected fields, with mandatory fields (*) and date formats.
	 * Param : file format
	 *
     * @Route("/export-file-model", name="integration_exportfilemodel")
     */
    public function exportFileModelAction()
    {
        return $this->render('OGAMBundle:Integration:export_file_model.html.twig', array(
            // ...
        ));
    }

}
