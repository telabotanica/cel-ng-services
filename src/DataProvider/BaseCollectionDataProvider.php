<?php

namespace App\DataProvider;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;

abstract class BaseCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    /** @var RequestStack */
    protected $requestStack;
    protected $repositoryManager;
    protected $security;

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

    abstract public function getResourceClass(string $resourceClass, string $operationName = null, array $context = []): bool;
    abstract public function supports(string $resourceClass, string $operationName = null, array $context = []): bool;

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
        $repository = $this->repositoryManager->getRepository($this->getResourceClass()->toString());

        if (!in_array($resourceClass, [Occurrence::class])) {
            throw new ResourceClassNotSupportedException();
        }

        $results = $repository->findWithRequest($request, $user);

        return $results;

    }

}

