<?php

namespace App\Repository;

use App\Entity\PnTbPair;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method PnTbPair|null find($id, $lockMode = null, $lockVersion = null)
 * @method PnTbPair|null findOneBy(array $criteria, array $orderBy = null)
 * @method PnTbPair[]    findAll()
 * @method PnTbPair[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PnTbPairRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PnTbPair::class);
    }

    // /**
    //  * @return PnTbPair[] Returns an array of PnTbPair objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PnTbPair
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
