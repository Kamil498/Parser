<?php

namespace App\Command;

use App\Entity\TaniaKsiazka;
use App\Service\PageDownloader;
use App\Service\TaniaExtract;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tania:worker',
    description: 'Process books from queue'
)]
class TaniaWorkerCommand extends Command
{
    private string $queue = 'tania-ksiazka-queue';
    private string $processed = 'tania-ksiazka-processed';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
        private PageDownloader $downloader,
        private TaniaExtract $extract
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        while (true) {

            $item = $this->redis->brpoplpush(
                $this->queue,
                $this->processed,
                5
            );

            if (!$item) {
                continue;
            }

            $data = json_decode($item, true);

            if (!is_array($data) || !isset($data['id'])) {
                $output->writeln('<error>Invalid queue item.</error>');

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                continue;
            }

            $product = $this->em
                ->getRepository(TaniaKsiazka::class)
                ->find($data['id']);

            if (!$product) {
                $output->writeln("<error>Book {$data['id']} not found.</error>");

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                continue;
            }

            try {

                $output->writeln('Downloading: ' . $product->getUrl());

                $product->setStatus('processing');
                $this->em->flush();

                $html = $this->downloader->download(
                    $product->getUrl()
                );

                $bookData = $this->extract->extract($html);

                $product->setTytul(
                    $bookData['tytul'] ?? null
                );

                $product->setAutor(
                    $bookData['autor'] ?? null
                );

                $product->setWydawnictwo(
                    $bookData['wydawnictwo'] ?? null
                );

                $product->setRokWydania(
                    $bookData['rok_wydania'] ?? null
                );

                $product->setCena(
                    $bookData['cena'] ?? null
                );

                $product->setStatus('done');

                $this->em->flush();

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                $this->redis->del(
                    'book_product:' . $product->getId()
                );

                $output->writeln('<info>OK</info>');

            } catch (\Throwable $e) {


                dump($e);
                die();

                $product->setStatus('error');
                $this->em->flush();

                $output->writeln('<error>' . $e->getMessage() . '</error>');

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );
            }
        }

        return Command::SUCCESS;
    }
}

