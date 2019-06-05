<?php

namespace App\Repository;

use App\Entity\UserOccurrenceTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 */
class PhotoTagRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, UserOccurrenceTag::class);
    }

    /**
     * @return UserOccurrenceTag[] Returns an array of UserOccurrenceTag 
     * entities with the given user id.
     */
    public function findByUserId($userId) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userId = :val2')
            ->setParameter('val2', $userId)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();
    }

}
