<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Invoice;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Invoice>
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    //    /**
    //     * @return Invoice[] Returns an array of Invoice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('i.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Invoice
    //    {
    //        return $this->createQueryBuilder('i')
    //            ->andWhere('i.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Permet de donner le chrono à une nouvelle facture
     *
     * @param User $user
     * @return integer|null
     */
    public function findNextChrono(User $user): ?int
    {
        try{
            return $this->createQueryBuilder("i")
                    ->select("i.chrono")
                    ->join("i.customer","c")
                    ->where("c.user = :user")
                    ->setParameter("user", $user)
                    ->orderBy("i.chrono","DESC")
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getSingleScalarResult() + 1;
        }catch(\Exception $e)
        {
            return 1;
        }
    }
}
