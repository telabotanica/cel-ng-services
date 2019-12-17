<?php

namespace App\Controller;

use App\Service\AbstractBulkService;

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
 * Abstract class implementing JSON-PATCH bulk operations. Currently, only 
 * 'remove', 'modify' and 'copy' operations are offered. 
 *
 * @package App\Controller
 *
 * @See Https://Blog.Apisyouwonthate.Com/Put-Vs-Patch-Vs-Json-Patch-208b3bfda7ac
 * @See Http://Restlet.Com/Company/Blog/2015/05/18/Implementing-Bulk-Updates-Within-Restful-Services/
 * @See Https://Tools.Ietf.Org/Html/Rfc6902
 * @See Http://Jsonpatch.Com/
 */
abstract class AbstractBulkAction {

    protected $abService;

    /**
     * Invokes the controller/action.
     *
     * @param Request $request The HTTP <code>Request</code> issued 
     *        by the client.
     * 
     * @return Response Returns an HTTP <code>Response</code> reflecting
     *         the action result.
     */
    public function __invoke(Request $request): Response {

        $operationsAsArray = array();
	    $content = $request->getContent();

        if ( $content == null ) {
		    $msgObj = array('message' => 'Empty request. Operation aborted');
		    // returns a 405:
		    return $this->instanciateResponse(
                json_encode($msgObj), Response::HTTP_UNPROCESSABLE_ENTITY);
	    }
	    else {

		    try {
                $bulkActionResponse = $this->abService->processActions($content);		                
            } catch (\Exception $ex) {
			    $msgObj = array('message' => 'Internal server error.');
			    // returns a 500:
			    return $this->instanciateResponse(
                    msgObj, 
                    Response::HTTP_INTERNAL_SERVER_ERROR);
		    }		
		    // Returns a 200 in case of success (as does restlet)
		    return $this->instanciateResponse(
                json_encode(
                    $bulkActionResponse->getMessage()), 
                    $bulkActionResponse->getStatusCode());

        }

    }

	private function instanciateResponse($content, $statusCode): Response {
        $headerArray = array('Content-Type' => 'application/json-patch+json');

		return new Response($content, $statusCode, $headerArray);
	}


}


