<?php

namespace App\Repository;

use App\Entity\Object2attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Object2attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Object2attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Object2attribute[]    findAll()
 * @method Object2attribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class Object2attributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Object2attribute::class);
    }

    // /**
    //  * @return Object2attribute[] Returns an array of Object2attribute objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Object2attribute
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
