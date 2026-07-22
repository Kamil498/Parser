<?php

namespace App\Command;

use App\Entity\Book;
use App\Entity\PriceHistory;
use App\Service\PageDownloader;
use App\Service\PriceExtractor;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'book:price:queue:worker',
    description: 'Process price queue'
)]
class BookPriceWorkerCommand extends Command
{
    private string $queue = 'book-price-queue';
    private string $processingQueue = 'book-price-processing';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
        private PageDownloader $downloader,
        private PriceExtractor $extractor
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
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

            $book = $this->em->getRepository(Book::class)->find($data['id']);

            if (!$book) {
                $this->redis->lrem(
                    $this->processingQueue,
                    1,
                    $item
                );

                continue;
            }
            dump($book->getUrl());


            try {

                $output->writeln('Start pobierania');

                $html = $this->downloader->download($book->getUrl());

                file_put_contents('bookland.html', $html);

                dump(substr($html, 0, 1500));

                $path = getcwd() . '/bookland.html';

                file_put_contents($path, $html);

                dump($path);


                $output->writeln('HTML pobrany');

                $price = $this->extractor->extract(
                    $html,
                );

                $history = new PriceHistory();
                $history->setBook($book);
                $history->setPrice($price);
                $history->setCheckedAt(new \DateTimeImmutable());

                $output->writeln('Persist');
                $this->em->persist($history);

                $book->setUpdatedAt(new \DateTimeImmutable());

                $this->em->flush();

                $output->writeln('OK');

                $this->redis->lrem(
                    $this->processingQueue,
                    1,
                    $item
                );

                $this->redis->del(
                    "queue:book:" . $book->getId()
                );

                $this->em->clear();

                $output->writeln('<info>OK</info>');

            } catch (\Throwable $e) {

                $output->writeln(
                    '<error>' . $e->getMessage() . '</error>'
                );

                $this->redis->lrem(
                    $this->processingQueue,
                    1,
                    $item
                );

                continue;
            }
        }

        return Command::SUCCESS;
    }
}
