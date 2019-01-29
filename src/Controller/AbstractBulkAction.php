<?php

namespace App\Controller;

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

/**
 * JSON-PATCH bulk operations. Currently, only 
 * 'remove', 'modify' and 'copy' operations are offered. 
 *
 * @todo improve return messages (e.g. no description for deleted records, wrong path for cloning  - not the cloned entity one).
 * @todo implement isValidPath(string $path) 
 */
// @see https://blog.apisyouwonthate.com/put-vs-patch-vs-json-patch-208b3bfda7ac
// @see http://restlet.com/company/blog/2015/05/18/implementing-bulk-updates-within-restful-services/
// @see https://tools.ietf.org/html/rfc6902
// @see http://jsonpatch.com/
// @refactor use generics
abstract class AbstractBulkAction
{
    protected $doctrine;
    protected $repo;
    protected $form;
    protected $factory;
    protected $serializer;
    protected $authorizationChecker;
    protected $jsonResp = array();

    public function __construct(RegistryInterface $doctrine, FormFactoryInterface $factory, ValidatorInterface $validator, AuthorizationCheckerInterface $authorizationChecker, HttpKernelInterface $kernel, SerializerInterface $serializer)
    {
        $this->doctrine = $doctrine;
        $this->factory = $factory;
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
     * @inheritdoc
     */
    public function __invoke(Request $request): Response {


        $operationsAsArray = array();
	    $content = $request->getContent();


        if ( $content == null ) {
		    $msgObj = array('message' => 'Empty request. Operation aborted');
		    // returns a 405:
		    return $this->instanciateResponse(json_encode($msgObj), Response::HTTP_UNPROCESSABLE_ENTITY);
	    }
	    else {


		    try {

		        $operationsAsArray = json_decode($content, true);

			    // for each operation described in the JSON: 
			    foreach ($operationsAsArray as $operation){
    			    $request = $this->subRequestForOperation($operation);	

				    if ( null !== $request ) {
					    $response = $this->kernel->handle($request, HttpKernelInterface::SUB_REQUEST);	
					    $path        = '';
					    if (array_key_exists ( 'path' , $operation ) ) {
					        $path = $operation['path'];
					    }

					    $this->jsonResp[] = $this->buildAtomicResponse($path, $response->getStatusCode(), json_decode($response->getContent()));
				    }
			    }
		    } catch (\Exception $ex) {
				echo var_dump($ex->getMessage());
			    $msgObj = array('message' => 'Internal server error.');
			    // returns a 500:
			    return $this->instanciateResponse(json_encode($this->jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR);
		    }		
		    // Returns a 200 in case of success (as does restlet)
		    return $this->instanciateResponse(json_encode($this->jsonResp), Response::HTTP_CREATED);

        }

    }

	private function instanciateResponse($content, $statusCode): Response {
		return new Response($content, $statusCode, array('Content-Type' => 'application/json-patch+json'));

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
	    

//	    else {
		    if ($op == 'remove') {

	            if ( !$isValidPath ) {
			        $this->jsonResp[] = $this->buildAtomicResponse($path, Response::HTTP_UNPROCESSABLE_ENTITY, 'Path ' . 'is either an invalid path or a path that is not handled by the CEL2 API.');
			        return null;
	            }
			    $method = Request::METHOD_DELETE;
		    }
		    else if ($op == 'replace') {

                	    if ( !$isValidPath ) {
			$this->jsonResp[] = $this->buildAtomicResponse($path, Response::HTTP_UNPROCESSABLE_ENTITY, 'Path ' . 'is either an invalid path or a path that is not handled by the CEL2 API.');
			return null;
	    }
			    $method = Request::METHOD_PATCH;
		    }
		    else if ($op == 'copy') {
                if ( null !== $from ) {
	                if ( !$isValidPath ) {
			            $this->jsonResp[] = $this->buildAtomicResponse($path, Response::HTTP_UNPROCESSABLE_ENTITY, 'Path ' . 'is either an invalid path or a path that is not handled by the CEL2 API.');
			            return null;
	                }
                    $fromId = $this->extractIdFromPath($from);

                    if ( null == $fromId ) {
                        $this->jsonResp[] = $this->buildAtomicResponse($from, Response::HTTP_UNPROCESSABLE_ENTITY, 'Malformed path: cannot extract id from it.');
                    }
                    else {
			            $toBeClonedOcc = $this->doctrine
                            ->getRepository(Occurrence::class)
                            ->find(intval($fromId));
                        if ( null == $toBeClonedOcc ) {
			                $this->jsonResp[] = $this->buildAtomicResponse($from, Response::HTTP_NOT_FOUND, 'Impossible to clone the occurrence : occurrence with id = ' . $fromId .'  cannot be found. The id is unknown.');
			                return null;

                        }
                        else {
                            $cloneOcc = clone $toBeClonedOcc;
                            $em = $this->doctrine
                                ->getManager();
                            $em->persist($cloneOcc);
                            $em->flush();
                            $this->jsonResp[] = $this->buildAtomicResponse($from, 201, json_decode($this->serializer->serialize($cloneOcc, 'json')) );
       //                     $value = json_decode($toBeClonedOcc);
            			    $method = Request::METHOD_POST;

                            return null;
                          
                        }
                    }
                }
                else{
                    $this->jsonResp[] = $this->buildAtomicResponse($from, Response::HTTP_UNPROCESSABLE_ENTITY, 'Malformed request: a from element is required for operation copy.');
                    return null;
                }
		    }
		    else {
			    $this->jsonResp[] = $this->buildAtomicResponse($path, Response::HTTP_METHOD_NOT_ALLOWED, 'Method not allowed - only allowed: remove, replace or copy. Méthode non permise. Sont autorisées : remove, replace ou copy');
			    return null;
		    }		


		$request = Request::create($path, $method, array(), array(), array(), array(), json_encode($value));
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


