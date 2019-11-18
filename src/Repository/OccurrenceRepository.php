<?php

namespace App\Repository;

use App\Entity\Occurrence;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

use Symfony\Bridge\Doctrine\RegistryInterface;

//@refactor transfer responsability for findBySignature to elastica repository+ deleteme
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

    /**
     * Returns true if an Occurrence with the same signature i.e.
     * same locality/geometry/userId/observedDate/serSciName already exists.
     * Else returns false.
     *
     * @return bool Returns true if an Occurrence with the same signature i.e.
     * locality/geometry/userId/observedDate/userSciName already exists.
     */
    public function hasDuplicate($occ) : bool {


        $result = $this->createQueryBuilder('p')
            ->andWhere('p.signature = :val')
            ->setParameter('val', $occ->getSignature())
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        return ( sizeof($result) > 0 );
    }


}
