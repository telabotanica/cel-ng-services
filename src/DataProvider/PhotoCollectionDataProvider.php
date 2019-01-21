<?php

namespace App\DataProvider;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

use App\Entity\Photo;

final class PhotoCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /** @var RequestStack */
    private $requestStack;
    private $repositoryManager;
    private $security;

    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @param RequestStack $requestStack
     */
    public function __construct(Security $security, RepositoryManagerInterface $repositoryManager, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->repositoryManager = $repositoryManager;
        $this->requestStack = $requestStack;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Photo::class === $resourceClass;
    }

    /**
     * Retrieves a collection of <code>Occurrence</code> instances.
     * 
     * @param string $resourceClass
     * @param string|null $operationName
     *
     * @throws ResourceClassNotSupportedException
     *
     * @return array|\Traversable
     */
    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $request = $this->requestStack->getCurrentRequest();
        $filters = $request->query->all();
        $user = $this->security->getToken()->getUser();


        /** @var SearchRepository $repository */
        $repository = $this->repositoryManager->getRepository('App:Photo');

        if (!in_array($resourceClass, [Photo::class])) {
            throw new ResourceClassNotSupportedException();
        }


        return $repository->findWithRequest($request, $user);

    }



}

