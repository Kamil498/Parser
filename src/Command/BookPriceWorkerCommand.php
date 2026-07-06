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
        while (true) {

            $item = $this->redis->lPop($this->queue);

            if (!$item) {
                sleep(2);
                continue;
            }

            $data = json_decode($item, true);

            $bookId = $data['id'];

            $output->writeln("Processing book ID: {$bookId}");

            /** @var Book|null $book */
            $book = $this->em->getRepository(Book::class)->find($bookId);

            if (!$book) {
                continue;
            }


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
            $this->em->clear();
        }

        return Command::SUCCESS;
    }

    private function fetchPrice(string $url): float
    {

        return rand(10, 200);
    }
}
