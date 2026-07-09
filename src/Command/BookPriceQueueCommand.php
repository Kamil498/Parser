<?php

namespace App\Command;

use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:price:queue:producer',
    description: 'Push books to price queue'
)]
class BookPriceQueueCommand extends Command
{
    private string $queue = 'book-price-queue';

    private EntityManagerInterface $em;
    private Client $redis;

    public function __construct(EntityManagerInterface $em, Client $redis)
    {
        parent::__construct();
        $this->em = $em;
        $this->redis = $redis;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();

        $books = $this->em->createQuery(
            "SELECT b FROM App\Entity\Book b
             WHERE b.next_checked_time < :now
             AND b.is_active = true"
        )
            ->setParameter('now', $now)
            ->setMaxResults(100)
            ->getResult();

        if (!$books) {
            $output->writeln('<info>No books to queue</info>');
            return Command::SUCCESS;
        }

        $queued=0;
        foreach ($books as $book) {

            $lockKey="queue:book:{$book->getId()}";

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
            $payload = [
                'id' => $book->getId(),
                'url' => $book->getUrl(),
                'shop' => $book->getShop(),
            ];

            $this->redis->rPush($this->queue, json_encode($payload));
            $queued++;
        }

        $output->writeln("<info>Queued {$queued} books</info>");

        return Command::SUCCESS;
    }
}
