<?php

namespace App\Repository;

use App\Entity\UserOccurrenceTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 */
// @refactor Make an AbstractHierarchicalEntityRepository for this and userocctag 
class UserOccurrenceTagRepository extends ServiceEntityRepository {

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
            ->getQuery()
            ->getResult();
    }

    /**
     * @return UserOccurrenceTag[] Returns an array of UserOccurrenceTag 
     * entities with the given name.
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
        return $this->getEntityManager()->createQuery("SELECT o FROM App:UserOccurrenceTag o WHERE o.userId =  :userId AND o.path = :parentName")
            ->setParameter('parentName', '%'.$tagName)
            ->setParameter('userId', $userId)
            ->getResult();
    }


    private function generateTagTree($entity, &$arr = [], $userId) {
        $name = $entity->getName();
//echo(var_dump($name) + "-");
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
