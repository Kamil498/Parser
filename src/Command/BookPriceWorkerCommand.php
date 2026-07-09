<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\PriceHistory;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:price:queue:worker',
    description: 'Process book price queue'
)]
class BookPriceWorkerCommand extends Command
{
    private string $queue = 'book-price-queue';
    private string $processingQueue = 'book-price-processing';

    public function __construct(private EntityManagerInterface $em, private Client $redis)
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {

        while ($item = $this->redis->rpop($this->processingQueue)) {
            $this->redis->lpush($this->queue, $item);
        }

        while (true) {

            $item = $this->redis->brpoplpush(
                $this->queue,
                $this->processingQueue,
                5
            );

            if (!$item) {
                continue;
            }

            $data = json_decode($item, true);

            $bookId = $data['id'];

            $output->writeln("Processing book ID: {$bookId}");


            $book = $this->em->getRepository(Book::class)->find($bookId);

            if (!$book) {

                $this->redis->lrem(
                    $this->processingQueue,
                    1,
                    $item
                );

                $this->redis->del("queue:book:$bookId");

                continue;
            }

            try {

                $price = $this->fetchPrice($book->getUrl());

                $history = new PriceHistory();
                $history->setBook($book);
                $history->setPrice($price);
                $history->setCheckedAt(new \DateTimeImmutable());

                $this->em->persist($history);

                $book->setNextCheckedTime(
                    new \DateTimeImmutable('+6 hours')
                );

                $this->em->flush();

                $this->redis->lrem(
                    $this->processingQueue,
                    1,
                    $item
                );

                $this->redis->del("queue:book:$bookId");

                $this->em->clear();

            } catch (\Throwable $e) {

                $output->writeln("<error>{$e->getMessage()}</error>");

                $this->em->clear();
            }
        }

        return Command::SUCCESS;
    }

    private function fetchPrice(string $url): float
    {
        return rand(10, 200);
    }
}
