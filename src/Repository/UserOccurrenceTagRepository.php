<?php

namespace App\Repository;

use App\Entity\UserOccurrenceTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * <code>ServiceEntityRepository</code> for <code>UserOccurrenceTag</code>
 * entities.
 *
 * @package App\Repository
 */
class UserOccurrenceTagRepository extends AbstractTagRepository {

    const FIND_CHILDREN_QUERY = 'SELECT o FROM App:UserOccurrenceTag o WHERE o.userId =  :userId AND o.path LIKE :parentName';

    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, UserOccurrenceTag::class);
    }

    /**
     *     * @return UserOccurrenceTag[] Returns an array of UserOccurrenceTag
     *     * entities with the given name.
     *
     */
    public function findByNameAndUserId($name, $userId) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.name = :val1')
            ->setParameter('val1', $name)
            ->andWhere('p.userId = :val2')
            ->setParameter('val2', $userId)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }


    protected function getFindChildrenQuery(): string {
        return UserOccurrenceTagRepository::FIND_CHILDREN_QUERY;
    }

}
