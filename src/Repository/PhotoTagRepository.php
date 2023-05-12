<?php

namespace App\Repository;

use App\Entity\PhotoTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 *
 * @method PhotoTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhotoTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhotoTag[]    findAll()
 * @method PhotoTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 */
class PhotoTagRepository extends ServiceEntityRepository {

//    const FIND_CHILDREN_QUERY = 'SELECT o FROM App:PhotoTag o WHERE o.userId =  :userId AND o.path LIKE :parentName';

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, PhotoTag::class);
    }

//    protected function getFindChildrenQuery(): string {
//        return PhotoTagRepository::FIND_CHILDREN_QUERY;
//    }

}
