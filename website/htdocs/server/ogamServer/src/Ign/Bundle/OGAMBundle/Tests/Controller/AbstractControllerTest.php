<?php
namespace Ign\Bundle\OGAMBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Ign\Bundle\OGAMBundle\Entity\Website\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Ign\Bundle\OGAMBundle\Entity\Generic\QueryForm;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

class AbstractControllerTest extends WebTestCase {

	protected $client = null;

	public function setUp() {
	    ini_set("max_execution_time", 960);
	    echo "\n\rStarting the access tests...\n\r";
	    $fullClassName = explode('\\', get_class($this));
	    $shortClassName = substr(end($fullClassName),0,-4);
	    echo "Controller: ", $shortClassName, "\n\r";
		$this->client = static::createClient();
	}

	protected function logIn($login = 'admin', $roles = array('ROLE_ADMIN')) {
	    echo "User Role: ", implode(" ,", $roles), "\n\r";
	    $session = $this->client->getContainer()->get('session');
		
		// the firewall context (defaults to the firewall name)
		$firewall = 'main';
		
		$token = new UsernamePasswordToken((new User())->setLogin($login), null, $firewall, $roles);
		$session->set('_security_' . $firewall, serialize($token));
		$session->save();
		
		$cookie = new Cookie($session->getName(), $session->getId());
		$this->client->getCookieJar()->set($cookie);
	}
	
	// *************************************************** //
	// Access Right Tests //
	// *************************************************** //
	
	/**
	 * Test access without login
	 */
	public function testControllerActionNotLoggedAccess() {
	    echo "User Role: NOT LOGGED\n\r";
		$this->checkControllerActionAccess($this->getNotLoggedUrls(), Response::HTTP_FOUND);
	}

	/**
	 * Test access with a visitor login
	 */
	public function testControllerActionVisitorAccess() {
		$this->logIn('visitor', array(
			'ROLE_VISITOR'
		)); // The session must be keeped for the chained requests
		$this->checkControllerActionAccess($this->getVisitorUrls(), Response::HTTP_FORBIDDEN);
	}

	/**
	 * Test access with a admin login
	 */
	public function testControllerActionAdminAccess() {
		$this->logIn('admin', array(
			'ROLE_ADMIN'
		)); // The session must be keeped for the chained requests
		$this->checkControllerActionAccess($this->getAdminUrls(), Response::HTTP_OK);
	}

	/**
	 * Tests all accesses to controller actions
	 */
	public function checkControllerActionAccess($urls, $defaultStatusCode = Response::HTTP_OK) {
		$client = $this->client;
		
		// Loop on the urls
		foreach ($urls as $urlName => $url) {
			echo "\n\r", $urlName, "...";
			
            // Set the request parameters
			$requestParameters = $url[0];
            if (empty($requestParameters['uri'])) {
                throw new \InvalidArgumentException("The 'uri' parameter is mandatory.");
            } else {
                $uri = $requestParameters['uri'];
            }
            $method = empty($requestParameters['method']) ? 'GET' : $requestParameters['method'];
            $parameters = empty($requestParameters['parameters']) ? array() : $requestParameters['parameters'];
            $sessionParameters = empty($requestParameters['sessionParameters']) ? array() : $requestParameters['sessionParameters'];

            // Set the response parameters
			$responseParameters = empty($url[1]) ? [] : $url[1];
			$statusCode = empty($responseParameters['statusCode']) ? $defaultStatusCode : $responseParameters['statusCode'];
			$contentFile = empty($responseParameters['contentFile']) ? null : $responseParameters['contentFile'];
			$isJson = empty($responseParameters['isJson']) ? false : $responseParameters['isJson'];
			$jsonFile = empty($responseParameters['jsonFile']) ? null : $responseParameters['jsonFile'];
			$redirectionLocation = empty($responseParameters['redirectionLocation']) ? '/user/login' : $responseParameters['redirectionLocation'];
			$alertMessage = empty($responseParameters['alertMessage']) ? null : $responseParameters['alertMessage'];
			
            // Set the session
            if(!empty($sessionParameters)){
                $sessionParametersOldsValues = [];
                $session = $client->getContainer()->get('session');
                foreach($sessionParameters as $key => $values) {
                    if($session->has($key)) {
                        $sessionParametersOldsValues[$key] = $session->get($key);
                    }
                    if (!isset($values['value'])) {
                        throw new \InvalidArgumentException("The 'value' is mandatory for a session parameter.");
                    } else {
                        $session->set($key, $values['value']);
                    }
                }
                $session->save();
            }

			// Launch the request
            $crawler = $client->request(
                $method,
                $uri,
                $parameters
            );

            // Clean the session
            if(!empty($sessionParameters)){
                $session = $client->getContainer()->get('session');
                foreach($sessionParameters as $key => $values) {
                    $isPermanent = empty($values['isPermanent']) ? false : $values['isPermanent'];
                    if(!$isPermanent){
                        if(isset($sessionParametersOldsValues[$key])) {
                            $session->set($key, $sessionParametersOldsValues[$key]);
                        } else {
                            $session->remove($key);
                        }
                    }
                }
                $session->save();
            }
			
			// Display the response status and error message
			$responseStatusCode = $client->getResponse()->getStatusCode();
			echo " Status code : ", $responseStatusCode;
			if ($isJson && $responseStatusCode === Response::HTTP_OK) {
				$response = json_decode($client->getResponse()->getContent());
				if ($response->success === true) {
					echo ", Success : true";
					// echo "\n\r", $client->getResponse()->getContent();
				} else {
					echo ", Success : ", $response->success ? 'true' : 'false', ", Error message : \n\r", $response->errorMessage;
				}
			}
			if ($responseStatusCode === Response::HTTP_INTERNAL_SERVER_ERROR) {
				echo ", Error message : ";
				print_r($crawler->filter('div#traces-text')->text());
			}
			
			// Asserts
			try {
				// Check the status code
				$this->assertEquals($statusCode, $responseStatusCode);
				// Check the content
				if ($contentFile !== null && $responseStatusCode === Response::HTTP_OK) {
					$this->assertStringEqualsFile($contentFile, $client->getResponse()
						->getContent());
				}
				// Check the redirection location
				if ($responseStatusCode === Response::HTTP_FOUND) {
					$location = $client->getResponse()->headers->get('Location');
					$this->assertTrue($client->getResponse()
						->isRedirect($redirectionLocation), "Response location '$location' doesn't match the requested location '$redirectionLocation'");
				}
				// Check the alert message
				if ($alertMessage !== null) {
					$crawler = $client->followRedirect();
					$this->assertEquals($alertMessage, trim($crawler->filter('div[role=alert]')
						->text()));
				}
				// Check the json success parameter and json content
				if ($isJson && $responseStatusCode === Response::HTTP_OK) {
					$this->assertEquals(true, $response->success);
					if ($jsonFile !== null) {
						$this->assertJsonStringEqualsJsonFile($jsonFile, $client->getResponse()
							->getContent());
					}
				}
			} catch (\Exception $e) {
				echo ", Assert error message : ", $e->getMessage();
				throw $e;
			}
		}
		echo "\n\n\r";
	}

	/**
	 * Provision list url
	 *
	 * Format : [
	 * [ // RequestParameters
	 *     'uri' => '/ControllerRoute/ActionRoute', // Required
	 *     'method' => 'GET|POST|...', // Default : GET
	 *     'parameters' => [ // Default : []
	 *         'name'=> value
     *     ],
     *     'sessionParameters' => [ // Default : []
     *         'name' => [
     *             'value' => value, // Required
     *             'isPermanent' => true|false // Default : false
     *         ]
	 *     ]
	 * ],[ // ResponseParameters
	 *     'statusCode' => Response::HTTP_OK|Response::HTTP_FOUND|..., // Default : Response::HTTP_OK
	 *     'contentFile' => __DIR__.'/Mock/MyController/myContentFile.json', // Default : null
	 *     'isJson' => true|false // Default : false
	 *     'jsonFile' => __DIR__.'/Mock/MyController/myJsonFile.json', // Default : null
	 *     'redirectionLocation' => '/ControllerRoute/ActionRoute', // Default : '/user/login'
	 *     'alertMessage' => 'The alert message.' // Default : null
	 * ]]
	 */
	public function getUrls() {
		return [];
	}

	public function getNotLoggedUrls() {
		return $this->getUrls();
	}

	public function getVisitorUrls() {
		return $this->getUrls();
	}

	public function getAdminUrls() {
		return $this->getUrls();
	}
}