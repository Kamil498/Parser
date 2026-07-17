<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'producer',
    description: 'Run selected producer(s)',
)]
class MainProducerCommand extends Command
{
    private array $producers = [
        'bookland-producer'      => 'book:bookland-producer',
        'tania-ksiazka-producer' => 'book:tania-ksiazka-producer',
        'bonito-producer'        => 'book:bonito-producer',
        'empik-producer'         => 'book:empik-producer',
    ];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $choices = array_keys($this->producers);
        $choices[] = 'all';

        $selected = $io->choice(
            'Wybierz producenta',
            $choices,
            'all',
            true // możliwość wyboru wielu
        );

        if (in_array('all', $selected, true)) {
            $selected = array_keys($this->producers);
        }

        foreach ($selected as $producer) {

            $io->section("Uruchamiam {$producer}");

            $process = new Process([
                PHP_BINARY,
                'bin/console',
                $this->producers[$producer],
            ]);

            $process->setTimeout(null);
            $process->run(function ($type, $buffer) use ($output) {
                $output->write($buffer);
            });

            if (!$process->isSuccessful()) {
                $io->error("Błąd podczas uruchamiania {$producer}");
            } else {
                $io->success("{$producer} zakończony.");
            }
        }

        return Command::SUCCESS;
    }
}
