<?php

namespace App\Command;

use App\Entity\Bonito;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:bonito-producer',
    description: 'Produce messages to the Bonito topic',
)]

class BonitoProducerCommand extends Command
{
    private string $queue = 'bonito';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        $item=$this->em->getRepository(Bonito::class)->findBy([
            'status' => 'pending'
        ]);

        if(!$item){
            $output->writeln("No books to process");
        }

        $queued=0;

        foreach($item as $book){

            $lockKey="book-product:{$book->getId()}";

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
                'id' => $book->getId(),
                'url' => $book->getUrl(),
                'shop'=> $book->getShop()
            ];

            $this->redis->rpush($this->queue,json_encode($payload));
            $queued++;
        }

        $output->writeln("{$queued} books queued");

        return Command::SUCCESS;

    }

}
