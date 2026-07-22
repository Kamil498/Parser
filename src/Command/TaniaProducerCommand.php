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
    private string $queue = 'tania-ksiazka-queue';

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $items = $this->em->getRepository(TaniaKsiazka::class)->findBy([
            'status' => 'pending',
        ]);

        if (count($items) === 0) {
            $output->writeln('<comment>No books to process.</comment>');
            return Command::SUCCESS;
        }

        $queued = 0;

        foreach ($items as $book) {

            $lockKey = 'book:' . $book->getId();

            $locked = $this->redis->set(
                $lockKey,
                1,
                'EX',
                3600,
                'NX'
            );

            if (!$locked) {
                $output->writeln(
                    sprintf(
                        '<comment>Book #%d skipped (already locked).</comment>',
                        $book->getId()
                    )
                );

                continue;
            }

            $payload = [
                'id'   => $book->getId(),
                'url'  => $book->getUrl(),
                'shop' => $book->getShop(),
            ];

            $queueSize = $this->redis->rpush(
                $this->queue,
                json_encode($payload, JSON_UNESCAPED_UNICODE)
            );

            $queued++;

            $output->writeln(
                sprintf(
                    '<info>Queued book #%d (queue size: %d)</info>',
                    $book->getId(),
                    $queueSize
                )
            );
        }

        $output->writeln('');
        $output->writeln("<info>Queued {$queued} book(s).</info>");

        return Command::SUCCESS;
    }
}
