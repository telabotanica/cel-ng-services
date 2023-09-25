<?php

namespace App\Repository;

use App\Entity\Photo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Photo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Photo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Photo[]    findAll()
 * @method Photo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PhotoRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Photo::class);
    }

    /**
     * @return Photo[] Returns an array of Photo entities with the given name.
     */
    public function findByOriginalNameAndUserId($name, $userId)
    {


        return $this->createQueryBuilder('p')
            ->andWhere('p.originalName = :val')
            ->setParameter('val', $name)
            ->andWhere('p.userId = :val1')
            ->setParameter('val1', $userId)
            ->getQuery()
            ->getResult();
    }
	
	public function findOneByOriginalNameStartingWith($imagePrefix, $userId)
	{
		return $this->createQueryBuilder('p')
					->andWhere('p.originalName LIKE :prefix')
					->setParameter('prefix', $imagePrefix . '%')
					->andWhere('p.userId = :val1')
					->setParameter('val1', $userId)
					->orderBy('p.id', 'DESC')
					->setMaxResults(1)
					->getQuery()
					->getOneOrNullResult();
	}
    
}
