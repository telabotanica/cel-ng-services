<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Model\ExportTotal;
use App\Repository\ExportTotalRepository;

final class ExportTotalProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $repository;

    public function __construct(ExportTotalRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getCollection(string $resourceClass, string $operationName = null,  array $context = [])
    {
        $filters = $context['filters'] ?? [];
        $resultats = $this->repository->findAll($filters);

        return $resultats;
//        return new JsonResponse($this->serializer->serialize($resultats, 'json', ['groups' => 'read']), 201, [], true);
    }

    public function getItem(string $resourceClass, $id_observation, ?string $operationName = null, array $context = [])
    {
        return $this->repository->find($id_observation);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExportTotal::class === $resourceClass;
    }
}
