<?php

namespace App\Repository;

use App\Entity\PhotoPhotoTagRelation;
use App\Entity\PhotoTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 *
 * @method PhotoPhotoTagRelation|null find($id, $lockMode = null, $lockVersion = null)
 * @method PhotoPhotoTagRelation|null findOneBy(array $criteria, array $orderBy = null)
 * @method PhotoPhotoTagRelation[]    findAll()
 * @method PhotoPhotoTagRelation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 */
class PhotoTagPhotoRepository extends ServiceEntityRepository {


    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, PhotoTag::class);
    }

}
