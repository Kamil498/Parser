<?php

namespace App\Repository;

use App\Entity\EmpikPriceHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class EmpikPriceHistoryRepository extends ServiceEntityRepository
{

    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry,
            EmpikPriceHistory::class
        );
    }


    public function getLastPrice(
        string $ean,
        string $shop
    ): ?EmpikPriceHistory
    {

        return $this->createQueryBuilder('h')
            ->where('h.ean = :ean')
            ->andWhere('h.shop = :shop')
            ->setParameter('ean',$ean)
            ->setParameter('shop',$shop)
            ->orderBy('h.updatedAt','DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

    }

}
