<?php

// src/App/EventListener/OccurrenceEventListener.php
namespace App\EventListener;


use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\Security\Core\Security;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * In case of collection GET request on occurrences endpoint, adds an 'X-count'
 * HTTP header to the response for pagination purpose.
 */
class XcountResponseListener
{

    private $repositoryManager;
    private $security;  
    private $tokenStorage;    

    public function __construct(Security $security, RepositoryManagerInterface $repositoryManager, TokenStorageInterface $tokenStorage) 
    {
        $this->repositoryManager = $repositoryManager;
        $this->security = $security;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * In case of collection GET request on occurrences endpoint, adds 
     * an 'X-count' HTTP header to the response for pagination purpose.
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {   

        $request = $event->getRequest(); 
//echo var_dump($this->security->getUser());
//echo var_dump($this->tokenStorage->getToken());
//echo var_dump($this->security->getUser());

        $user = $this->security->getUser();
        $responseHeaders = $event->getResponse()->headers;

        if ( $request->attributes->get('_route') === "api_occurrences_get_collection") {

            $repository = $this->repositoryManager->getRepository('App:Occurrence');

            $results = $repository->countWithRequest($request, $user);
            //$results = 600;
            $responseHeaders->set('X-count', $results);
        }


    } 

}
