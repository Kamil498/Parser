<?php

namespace App\Command;

use App\Entity\TaniaKsiazka;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:tania-ksiazka-producer',
    description: 'Tania producer command',
)]

class TaniaProducerCommand extends Command
{

    private string $queue="tania-ksiazka-queue";

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $item = $this->em->getRepository(TaniaKsiazka::class)->findBy([
            'status'=>"pending"
        ]);

        if(!$item){
            $output->writeln("No books to process");
            return Command::SUCCESS;
        }

        $queued=0;

        foreach($item as $book){

            $LockKey="book:". $book->getId();

            $locked=$this->redis->set(
                $LockKey,
                1,
                'EX',
                3600,
                'NX'
            );

            if($locked){
                continue;
            }

            $payload=[
                'id'=>$book->getId(),
                'url'=>$book->getUrl(),
                'shop'=>$book->getShop()
            ];

            $this->redis->rpush(
                $this->queue,
                json_encode($payload)
            );

            $queued++;

        }
        $output->writeln("Queued $queued books");
        return Command::SUCCESS;
    }
}
