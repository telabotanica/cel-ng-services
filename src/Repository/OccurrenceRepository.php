<?php

namespace App\Repository;

use App\Entity\Occurrence;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

//@refactor transfer responsability for findBySignature to elastica repository+ deleteme
/**
 * @method Occurrence|null find($id, $lockMode = null, $lockVersion = null)
 * @method Occurrence|null findOneBy(array $criteria, array $orderBy = null)
 * @method Occurrence[]    findAll()
 * @method Occurrence[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OccurrenceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Occurrence::class);
    }

    /**
     * @return \App\Entity\UserOccurrenceTag[] Returns an array of UserOccurrenceTag
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
