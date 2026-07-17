<?php

namespace App\Command;

use App\Entity\Bonito;
use App\Service\BonitoExtractor;
use App\Service\PageDownloader;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'bonito:worker',
    description: 'Process Bonito messages from the queue',
)]
class BonitoWorkerCommand extends Command
{
    private string $queue = 'bonito';
    private string $processed = 'bonito:processed';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
        private PageDownloader $downloader,
        private BonitoExtractor $extractor
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

            if (!$data || !isset($data['id'])) {
                $output->writeln('Invalid message');

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                continue;
            }

            $product = $this->em
                ->getRepository(Bonito::class)
                ->find($data['id']);

            if (!$product) {
                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                continue;
            }

            try {

                $output->writeln('Product download ' .$product->getUrl());

                $product->setStatus('processing');

                $this->em->flush();

                $html = $this->downloader->download(
                    $product->getUrl()
                );

                $result = $this->extractor->extract($html);

                $product->setTytul(
                    $result['tytul'] ?? ''
                );

                $product->setAutor(
                    $result['autor'] ?? ''
                );

                $product->setWydawnictwo(
                    $result['wydawnictwo'] ?? ''
                );

                $product->setRokWydania(
                    $result['rok'] ?? null
                );

                $product->setCena(
                    $result['cena'] ?? '0'
                );

                $product->setStatus('done');

                $this->em->flush();


                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );


                $this->redis->del(
                    "book-product:" . $product->getId()
                );

                $output->writeln(
                    "OK: {$product->getId()}"
                );

            } catch (\Throwable $e) {

                $output->writeln(
                    "ERROR: " . $e->getMessage()
                );

                $product->setStatus('error');

                $this->em->flush();

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
