<?php

namespace App\Command;

use App\Entity\Empik;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes\AdditionalProperties;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'empik-price-producer',
    description: 'Add Empik products to price update queue'
)]

class EmpikPriceProducerCommand extends Command
{

    private string $queue='empik:price:queue';

    public function __construct(private Client $redis,
                                private EntityManagerInterface $em,){

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface$output): int
    {
        $books=$this->em->getRepository(Empik::class)->findBy([
            'status'=>"done"
    ]);

        $count=0;

        foreach($books as $book){

            $lockKey='empik:price:'.$book->getId();

            $locked=$this->redis->set(
                $lockKey,
                1,
                'EX',
                3600,
                'NX'
            );

            if(!$locked){
                continue;
            }

            $payload=[
                'id'=>$book->getId(),
                'url'=>$book->getUrl(),
            ];

            $this->redis->rpush($this->queue,json_encode($payload));

            $count++;

        }

        $output->writeln("Queued {$count} price checks");


        return Command::SUCCESS;

    }

}
