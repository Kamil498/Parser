<?php

namespace App\Command;


use App\Entity\Empik;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:empik-producer',
    description: 'Empik producer command',
)]

class EmpikProducerCommand extends Command
{

    private string $queue = 'book_product_queue';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $books=$this->em->getRepository(Empik::class)->findBy([
            'status' => 'pending'
        ]);

        if(!$books){
            $output->writeln("No books to process");
            return Command::SUCCESS;
        }

        $queued=0;

        foreach($books as $book){

            $lockKey="empik:{$book->getId()}";

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
                'shop'=>$book->getShop()
            ];

            $this->redis->rpush($this->queue, json_encode($payload));

            $queued++;

            $output->writeln("{$queued} books queued");


        }





        return Command::SUCCESS;
    }

}
