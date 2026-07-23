<?php

namespace App\Service;

use App\Entity\Empik;
use App\Entity\EmpikPriceHistory;
use App\Repository\EmpikPriceHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;


class PriceHistoryService
{

    public function __construct(
        private EntityManagerInterface $em,
        private EmpikPriceHistoryRepository $repository
    )
    {

    }



    public function updateEmpikPrice(
        Empik $empik,
        ?string $price
    ):void
    {


        if(
            $price===null ||
            $empik->getEan()===null
        ){
            return;
        }



        $last=$this->repository->getLastPrice(
            $empik->getEan(),
            $empik->getShop()
        );



        if(
            $last &&
            bccomp(
                $last->getPrice(),
                $price,
                2
            )===0
        ){

            $last->refreshUpdatedAt();

            return;
        }



        $history=new EmpikPriceHistory();


        $history->setEan(
            $empik->getEan()
        );


        $history->setShop(
            $empik->getShop()
        );


        $history->setPrice(
            (string)$price
        );


        $this->em->persist($history);

    }

}
