<?php

namespace App\Command;

use App\Entity\ProductBook;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'book:product-producer',
    description: 'push book product to queue producer',
)]

class BookProductProducerCommand extends Command
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
        $books=$this->em->getRepository(ProductBook::class)->findBy(
            [
                'status'=>'pending'
            ],null,100
        );

        if(!$books){
            $output->writeln('No book to process');
            return Command::SUCCESS;
        }

        $queued=0;

        foreach($books as $book){
            $lockKey = "book_product:{$book->getId()}";

            $locked = $this->redis->set(
                $lockKey,
                1,
                'EX',
                3600,
                'NX'
            );

            if(!$locked){
                continue;
            }

            $payload = [
                'id'=>$book->getId(),
                'url'=>$book->getUrl(),
                'shop'=>$book->getShop(),
            ];

            $this->redis->rPush($this->queue,json_encode($payload));

            $queued++;

        }
        $output->writeln("{$queued} books queued");

        return Command::SUCCESS;
    }

}


