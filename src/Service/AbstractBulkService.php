<?php

namespace App\Service;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use App\Entity\Occurrence;


class AbstractBulkServiceResponse {

    private $statusCode;
    private $message;

    public function __construct(String $message, int $statusCode) {
        $this->statusCode = $statusCode;
        $this->message = $message;
    }

	public function getStatusCode(): int {
		return $this->statusCode;
	}

	public function setStatusCode(int $statusCode){
		$this->statusCode = $statusCode;
	}

	public function getMessage(): String{
		return $this->message;
	}

	public function setMessage(String $message){
		$this->message = $message;
	}

}

/**
 * Abstract class implementing JSON-PATCH bulk operations. Currently, only 
 * 'remove', 'modify' and 'copy' operations are offered. 
 *
 * @package App\Service
 *
 * @See Https://Blog.Apisyouwonthate.Com/Put-Vs-Patch-Vs-Json-Patch-208b3bfda7ac
 * @See Http://Restlet.Com/Company/Blog/2015/05/18/Implementing-Bulk-Updates-Within-Restful-Services/
 * @See Https://Tools.Ietf.Org/Html/Rfc6902
 * @See Http://Jsonpatch.Com/
 */
abstract class AbstractBulkService {

    protected $doctrine;
    protected $repo;
    protected $form;
    protected $formFactory;
    protected $serializer;
    protected $authorizationChecker;
    protected $jsonResp = array();

    const INVALID_PATH_MSG = 'Given path is either an invalid path or a path' .
        ' that is not handled by the CEL2 API.';
    const MALFORMED_PATH_MSG = 'Malformed path: cannot extract id from it.';
    const ID_NOT_FOUND_MSG = 'Impossible to clone the resource : no resource' .
        ' with id = ';
    const MALFORMED_REQUEST_MSG = 'Malformed request: a from element is ' .
        'required for operation copy.';
    const METHOD_NOT_ALLOWED = 'Method not allowed - only allowed: remove, ' .
        'replace or copy. Méthode non permise. Sont autorisées : remove, ' .
        'replace ou copy';

    /**
     * Returns a new <code>AbstractBulkAction</code> instance 
     * initialized with (injected) services passed as parameters.
     *
     * @param RegistryInterface $doctrine The injected 
     *        <code>RegistryInterface</code> service.
     * @param FormFactoryInterface $formFactory The injected 
     *        <code>FormFactoryInterface</code> service.
     * @param ValidatorInterface $validator The injected 
     *        <code>ValidatorInterface</code> service.
     * @param AuthorizationCheckerInterface $authorizationChecker The injected
     *        <code>AuthorizationCheckerInterface</code> service.
     * @param SerializerInterface $serializer The injected 
     *        <code>SerializerInterface</code> service.
     * @param HttpKernelInterface $kernel The injected 
     *        <code>HttpKernelInterface</code> service.
     * @return AbstractBulkAction Returns a new  
     *         <code>AbstractBulkAction</code> instance initialized 
     *         with (injected) services passed as parameters.
     */
    public function __construct(
        RegistryInterface $doctrine, FormFactoryInterface $formFactory, 
        ValidatorInterface $validator, 
        AuthorizationCheckerInterface $authorizationChecker, 
        HttpKernelInterface $kernel, SerializerInterface $serializer) {

        $this->doctrine = $doctrine;
        $this->formFactory = $formFactory;
        $this->serializer = $serializer;
        $this->kernel = $kernel;
        $this->authorizationChecker = $authorizationChecker;
        $this->initRepo();
    }

    /**
     * Inits the form used for validation.
     */
    abstract protected function initForm($entity);

    /**
     * Inits the Doctrine repository.
     */
    abstract protected function initRepo();

    /**
     * @todo implement.
     */
    private function isValidPath(string $path) {
	    return true;
    }

    private function extractIdFromPath(string $path) {

        $bits = explode('/', $path);

        if ( null !== $bits && sizeof($bits) == 4 ) {
        	return $bits[3];
        }
        return null;
    }

    /**
     * Invokes the controller/action.
     *
     * @param Request $request The HTTP <code>Request</code> issued 
     *        by the client.
     * 
     * @return Response Returns an HTTP <code>Response</code> reflecting
     *         the action result.
     */
    public function processActions(String $requestContent): AbstractBulkServiceResponse {

        $operationsAsArray = array();

        if ( $requestContent == null ) {
		    $msgObj = array('message' => 'Empty request. Operation aborted');
		    // returns a 405:
		    return new AbstractBulkServiceResponse(
                json_encode($msgObj), Response::HTTP_UNPROCESSABLE_ENTITY);
	    }
	    else {
	        $operationsAsArray = json_decode($requestContent, true);

		    // for each operation described in the JSON: 
		    foreach ($operationsAsArray as $operation){
			    $request = $this->subRequestForOperation($operation);	

			    if ( null !== $request ) {
				    $response = $this->kernel->handle(
                        $request, HttpKernelInterface::SUB_REQUEST);	
				    $path        = '';
				    if (array_key_exists ( 'path' , $operation ) ) {
				        $path = $operation['path'];
				    }

				    $this->jsonResp[] = $this->buildAtomicResponse(
                        $path, $response->getStatusCode(), 
                        json_decode($response->getContent()));
			    }
		    }
	
    	    // Returns a 200 in case of success (as does restlet)
    	    return new AbstractBulkServiceResponse(
                json_encode($this->jsonResp), Response::HTTP_CREATED);

        }

    }

	/**
 	 *  Returns a Symfony Sub request to perform given operation.
	 */
    private function subRequestForOperation($operation) {

		$value = '';
		$path = '';
		$method = '';
		$op = '';

		if (array_key_exists ( 'path' , $operation ) ) {
		    $path        = $operation['path'];
		}
		if (array_key_exists ( 'op' , $operation ) ) {
		    $op        = $operation['op'];
		}
		if (array_key_exists ( 'from' , $operation ) ) {
		    $from        = $operation['from'];
		}
		if (array_key_exists ( 'value' , $operation ) ) {
		    $value        = $operation['value'];
		}

	    $isValidPath = $this->isValidPath($path); 

	    if ($op == 'remove') {

            if ( !$isValidPath ) {
		        $this->jsonResp[] = $this->buildAtomicResponse(
                    $path, Response::HTTP_UNPROCESSABLE_ENTITY, 
                    AbstractBulkAction::INVALID_PATH_MSG);
		        return null;
            }
		    $method = Request::METHOD_DELETE;
	    }
	    else if ($op == 'replace') {

    	    if ( !$isValidPath ) {
		        $this->jsonResp[] = $this->buildAtomicResponse(
                    $path, Response::HTTP_UNPROCESSABLE_ENTITY, 
                    AbstractBulkAction::INVALID_PATH_MSG);
		        return null;
            }
		    $method = Request::METHOD_PATCH;
	    }
	    else if ($op == 'copy') {
            if ( null !== $from ) {
                if ( !$isValidPath ) {
		        $this->jsonResp[] = $this->buildAtomicResponse(
                    $path, Response::HTTP_UNPROCESSABLE_ENTITY, 
                    AbstractBulkAction::INVALID_PATH_MSG);
		            return null;
                }
                $from = str_replace(getenv('APP_PREFIX_PATH'), '', $from);
                $fromId = $this->extractIdFromPath($from);

                if ( null == $fromId ) {
                    $this->jsonResp[] = $this->buildAtomicResponse(
                        $from, Response::HTTP_UNPROCESSABLE_ENTITY, 
                        AbstractBulkAction::MALFORMED_PATH_MSG);
                }
                else {
		            $toBeClonedOcc = $this->repo
                        ->find(intval($fromId));
                    if ( null == $toBeClonedOcc ) {
		                $this->jsonResp[] = $this->buildAtomicResponse(
                            $from, Response::HTTP_NOT_FOUND, 
                            AbstractBulkAction::ID_NOT_FOUND_MSG . $fromId);

		                return null;

                    }
                    else {
                        $cloneOcc = clone $toBeClonedOcc;
                        $em = $this->doctrine
                            ->getManager();
                        $em->persist($cloneOcc);
                        $em->flush();
                        $ser = $this->serializer;
                        $serCloneOcc = $ser->serialize($cloneOcc, 'json');
                        $this->jsonResp[] = $this->buildAtomicResponse(
                            $from, 201, 
                            json_decode($serCloneOcc) );
        			    $method = Request::METHOD_POST;

                        return null;
                      
                    }
                }
            }
            else{
                $this->jsonResp[] = $this->buildAtomicResponse(
                    $from, Response::HTTP_UNPROCESSABLE_ENTITY, 
                    AbstractBulkAction::MALFORMED_REQUEST_MSG);
                return null;
            }
	    }
	    else {
		    $this->jsonResp[] = $this->buildAtomicResponse(
                $path, Response::HTTP_METHOD_NOT_ALLOWED, 
                AbstractBulkAction::METHOD_NOT_ALLOWED);
		    return null;
	    }		
            $path = str_replace(getenv('APP_PREFIX_PATH'), '', $path);
		$request = Request::create(
            $path, $method, array(), array(), array(), 
            array(), json_encode($value));

		$request->headers->set('Content-Type', 'application/json');

		return $request;

	}

    private function buildAtomicResponse($id, $status, $body) {
         return array(
            $id => array(
                'index' => $id,
                'status' => $status,
                'message' => $body
            )
        );
    }

}

