<?php
namespace OGAMBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller {

	/**
	 * @Route("/", name="homepage")
	 */
	public function indexAction() {
		$userRepo = $this->getDoctrine()->getRepository('OGAMBundle\Entity\Website\User', 'website');
		$users = $userRepo->findAll();

		$appRepo = $this->getDoctrine()->getRepository('OGAMBundle\Entity\Website\ApplicationParameter', 'website');
		$applicationParameters = $appRepo->findAll();

		$modeRepo = $this->getDoctrine()->getRepository('OGAMBundle\Entity\Metadata\Mode', 'metadata');
		$mode = $modeRepo->find(array('unit' => 'PROVIDER_ID', 'code' => '1'));

		$tableFieldRepo = $this->getDoctrine()->getRepository('OGAMBundle\Entity\Metadata\TableField', 'metadata');
		$tableField = $tableFieldRepo->find(array('format' => 'PLOT_DATA', 'data' => 'COMMENT'));

		$layersRepo = $this->getDoctrine()->getRepository('OGAMBundle\Entity\Mapping\Layer', 'mapping');
		$layers = $layersRepo->findAll();

		return $this->render('OGAMBundle:Default:index.html.twig', array(
			'users' => $users,
			'applicationParameters' => $applicationParameters,
			'tableField' => $tableField,
			'mode' => $mode,
			'layers' => $layers
		));
	}


	/**
	 * Test d'une route "admin".
	 *
	 * @Route("/admin/")
	 */
	public function adminAction()
	{
		return new Response('<html><body>Admin page!</body></html>');
	}
}
