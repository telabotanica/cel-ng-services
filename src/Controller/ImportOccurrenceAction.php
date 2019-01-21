<?php

namespace App\Controller;

use App\Form\OccurrenceType;
use App\Utils\FromArrayOccurrenceCreator;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Imports Occurrence resources by uploading a spreadsheet file (csv or excel).
 *
 * Importe des occurrences à partir d'un fichier de type "tableur" (csv ou 
 * excel).
 */
final class ImportOccurrenceAction
{
    private $validator;
    private $doctrine;
    private $factory;
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage, RegistryInterface $doctrine, FormFactoryInterface $factory, ValidatorInterface $validator)
    {
	$this->tokenStorage = $tokenStorage;
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
    }

    public function extractArrayFromSpreadsheet($file)
    {
	// @refactor: externalize this to conf.
        $file_mimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
         
        if( in_array($file->getMimeType(), $file_mimes) ) {
		        
            $extension = $file->getClientOriginalExtension();
         
            // @todo use an elvis operator here
            if('csv' == $extension) {
                $reader = new Csv();
            } else {
                $reader = new Xlsx();
            }
	    $reader->setReadDataOnly(true);
	    $file->move('/tmp/', $file->getClientOriginalName());
            $spreadsheet = $reader->load('/tmp/' . $file->getClientOriginalName());
             
            return $spreadsheet->getActiveSheet()->toArray();
        }
        return null;


    }

    /*
     * @refactor: the connection is flushed AFTER EACH INSERT to allow to
     * retrieve the newly created occ id... nice for feedbacks, sucks big
     * time for performances... @todo insert idless messages in $jsonResp
     * and update $jsonResp with ids afterward after the periodical flush.
     * @perf @todo: optimize this...
     */
    public function __invoke(Request $request): Response
    {
        $em = $this->doctrine->getManager();
		$occRepo = $em->getRepository('App\Entity\Occurrence');
	$user = $this->getUser();
        $file = $request->files->get('file');
        $occArray = $this->extractArrayFromSpreadsheet($file);
		// @todo ? inject $doctrine in the object
        $fromArrayOccCreator = new FromArrayOccurrenceCreator($this->doctrine);
        // Isolate the import in a transaction to allow rollbacking the INSERTs
        // in case an Exception occurs so the DB isn't left in a messy state.
        $em->getConnection()->beginTransaction();
        $jsonResp = array();
        $occCount = 0;

        try {
            foreach( $occArray as $occAsArray ) {
                $occCount++;
                $occ = $fromArrayOccCreator->transform($occAsArray, $user);

		if ( null !== $occ) {

		       // $form = $this->factory->create(OccurrenceType::class, $occ);

		        //if ( $form->submit() && $form->isValid() ) {                
			if ( $errors = $this->validator->validate($occ) ) {
        		    // persist the occurrence and associated photos alongside 
	            // (consequently updating elasticsearch 'photos' index) 
	            $em->persist($occ);	
			    $em->flush();

			    // Look for duplicate occurrences (same signature):
			    $occ->generateSignature();
			    $sign = $occ->getSignature();
			    $duplicates = $occRepo->findBySignature($sign, $user);
			    // If there is, at least, one duplicate, return null:
			    if ( sizeof($duplicates)>0 ) {
		            $jsonResp[] = $this->buildAtomicResponse($occ->getId(), Response::HTTP_CREATED, null, $occAsArray, 'Occurrence succesfully imported. Warining: possible duplicate already exists in DB. Observation importée avec succès. Attention toutefois : un doublon existe dans le carnet. Line/ligne:' . (string)$occCount );
			    }
                else {
		            $jsonResp[] = $this->buildAtomicResponse($occ->getId(), Response::HTTP_CREATED, null, $occAsArray, 'Occurrence succesfully imported. Observation importée avec succès. Line/ligne:' . (string)$occCount );
                }


		

	        }
	        else {
		        $errors = $this->validator->validate($occ);
	            $jsonResp[] = $this->buildAtomicResponse(-1, Response::HTTP_UNPROCESSABLE_ENTITY, null, $occAsArray, $errors);

			// @todo Check if nicely rendered
	            throw new ValidationException($this->validator->validate($occ));
	        }
/*
		        if ($occCount%25) {
		            $em->flush();
		        }
*/
		}
		else {
			// First line: headers => we don't log anything:
			if ( $occCount>1 ) {
				$jsonResp[] = $this->buildAtomicResponse(-1, Response::HTTP_UNPROCESSABLE_ENTITY, null, $occAsArray, 'Could not import occurrence. Would there be a duplicate (same signature)? Import impossible : un doublon (même signature) est-il déjà présent ? Line/ligne:' . (string)$occCount );
			}
		}
            } 
            $em->flush();
            $em->getConnection()->commit();
        } catch (Exception $e) {
            $em->getConnection()->rollBack();

            // swallow the Exception and inform the caller using the exception message
		// @todo Check if nicely rendered
            $jsonResp[] = $this->buildAtomicResponse(-1, 500, null, $occAsArray, $e.getMessage());
            return new Response(json_encode($jsonResp), Response::HTTP_INTERNAL_SERVER_ERROR, $e.getMessage());
        }
	$jsonResp = new Response(json_encode($jsonResp), Response::HTTP_MULTI_STATUS, []);


        return $jsonResp;
    }

    private function buildAtomicResponse($id, $status, $header, $body, $comment) {
         return array(
            $id => array(
                'status' => $status,
                'header' => $header,
                'comment' => $comment,
                'body' => $body
            )
        );
    }

    protected function getUser()
    {
        if (!$this->tokenStorage) {
            throw new \LogicException('The SecurityBundle is not registered in your application.');
        }

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        if (!is_object($user = $token->getUser())) {
            // e.g. anonymous authentication
            return;
        }

        return $user;
    }

}


