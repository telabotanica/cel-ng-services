<?php

namespace App\Repository;

use App\Entity\Occurrence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method UserOccurrenceTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserOccurrenceTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserOccurrenceTag[]    findAll()
 * @method UserOccurrenceTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
//@todo deleteme
class OccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Occurrence::class);
    }

    /**
     * @return UserOccurrenceTag[] Returns an array of UserOccurrenceTag 
     * entities with the given name.
     */
    public function findBySignature($signature, $user)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.signature = :val1')
            ->setParameter('val1', $signature)
            ->andWhere('o.userId = :val2')
            ->setParameter('val2', $user->getId())
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult()
        ;
    }

}
