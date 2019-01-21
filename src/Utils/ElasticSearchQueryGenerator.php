<?php

namespace App\Utils;

use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use ApiPlatform\Core\Metadata\Resource\Factory\ResourceMetadataFactoryInterface;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use FOS\ElasticaBundle\Manager\RepositoryManagerInterface;
use App\Entity\Occurrence;


final class ElasticSearchQueryGenerator
{


    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @param RequestStack $requestStack
     */
    static public function generateQuery($user, $filters)
    {
        $esQuery = new \Elastica\Query();
        ElasticSearchQueryGenerator::customizeWithFilters($esQuery, $filters);
        ElasticSearchQueryGenerator::customizeWithUserInfo($esQuery, $user);
        ElasticSearchQueryGenerator::customizeWithPagingParameters($esQuery, $filters);
        ElasticSearchQueryGenerator::customizeWithSortParameters($esQuery, $filters);

        return $esQuery;
    }

    /**
     * @param RepositoryManagerInterface $repositoryManager
     * @param RequestStack $requestStack
     */
    static public function customizeWithFilters($esQuery, $filters)
    {
        $esQuery->setFieldQuery('observer', 'string');

        return $esQuery;
    }


    /**
     */
    static public function customizeWithUserInfo($esQuery, $user)
    {
        if (!$user->isTelaBotanicaAdmin()) {
            if ($user->isProjectAdmin()) {
                $esQuery->setFieldQuery('projectId', $user->getAdministeredProjectId());
            }
            else if (!is_null($user)){
                $esQuery->setFieldQuery('userId', $user->getId());
            }
            else {
                $esQuery->setFieldQuery('isPublic', 'true');
            }
        }

        return $esQuery;
    }

    /**
     */
    static public function customizeWithPagingParameters($esQuery, $filters)
    {
        $esQuery->setFieldQuery('observer', 'string');

        return $esQuery;
    }

    /**
     */
    static public function customizeWithSortParameters($esQuery, $filters)
    {
        $esQuery->setFieldQuery('observer', 'string');

        return $esQuery;
    }


}



