<?php

namespace App\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Model\ExportTotal;
use App\Repository\ExportTotalRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final class ExportTotalProvider implements CollectionDataProviderInterface, ItemDataProviderInterface, RestrictedDataProviderInterface
{
    private $repository;
    private $serializer;

    public function __construct(ExportTotalRepository $repository, SerializerInterface $serializer)
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
    }

    public function getCollection(string $resourceClass, string $operationName = null,  array $context = [])
    {
        $filters = $context['filters'] ?? [];
        return $this->repository->findAll($filters);
//        return $resultats;
//        return new JsonResponse($this->serializer->serialize($resultats, 'json', ['groups' => 'exportTotal_read']), 201, [], true);
    }

    public function getItem(string $resourceClass, $id, ?string $operationName = null, array $context = [])
    {
        return $this->repository->find($id);
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExportTotal::class === $resourceClass;
    }
}
