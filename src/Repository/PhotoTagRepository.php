<?php

namespace App\Repository;

use App\Entity\PhotoTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 */
// @refactor Make an AbstractHierarchicalEntityRepository for this and userocctag 
class PhotoTagRepository extends ServiceEntityRepository {

    public function __construct(RegistryInterface $registry) {
        parent::__construct($registry, PhotoTag::class);
    }

    /**
     * @return PhotoTag[] Returns an array of PhotoTag 
     * entities with the given user id.
     */
    public function findByUserId($userId) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.userId = :val2')
            ->setParameter('val2', $userId)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findByPathAndUserId($path, $userId) {
        return $this->createQueryBuilder('p')
            ->andWhere('p.path = :val1')
            ->setParameter('val1', $path)
            ->andWhere('p.userId = :val2')
            ->setParameter('val2', $userId)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getTagTree($userId) {
        $tree = [];
        $rootTags = $this->findByPathAndUserId('/', $userId);
        $tagHierarchy = array();
        foreach($rootTags as $rootTag) {
            $rootTagNames = $rootTag->getName();
            $tagHierarchy[] = $this->generateTagTree($rootTag, $tree, $userId);

        }

        return $tree;
    }

    public function findChildren($tagName, $userId) {
        return $this->getEntityManager()->createQuery("SELECT o FROM App:PhotoTag o WHERE o.userId =  :userId AND o.path LIKE :parentName")
            ->setParameter('parentName', '%'.$tagName)
            ->setParameter('userId', $userId)
            ->getResult();
    }

    private function generateTagTree($entity, &$arr = [], $userId) {
        $name = $entity->getName();
        $children = $this->findChildren($entity->getName(), $userId);
        $arr += [$entity->getName() => null];

        if(count($children) > 0) {
            $arr[$name] = [];
            foreach($children as $child) {
                $this->generateTagTree($child, $arr[$name], $userId);
            } 
        } 

        return $arr;
    }

}
