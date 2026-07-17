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
    private string $queue="tania-ksiazka-queue";

    private string $processed="tania-ksiazka-processed";

    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
        private PageDownloader $downloader,
        private TaniaExtract $extract
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input,OutputInterface $output): int
    {
        while(true){
            $item=$this->redis->brpoplpush(
                $this->queue,
                $this->processed,
                5
            );
            if($item){
                continue;
            }

            $data=json_decode($item,true);

            $product=$this->em->getRepository(TaniaKsiazka::class)->find($data['id']);

            if (!$product) {
                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );
                continue;
            }

            try{
                $output->writeln('Product download ' .$product->getUrl());

                $product->setStatus('processing');

                $html=$this->downloader->download(
                    $product->getUrl()
                );

                $data = $this->extractor->extract($html);


                $product->setTytul(
                    $data['tytul']
                );

                $product->setAutor(
                    $data['autor']
                );

                $product->setWydawnictwo(
                    $data['wydawnictwo']
                );

                $product->setRokWydania(
                    $data['rok_wydania']
                );

                $product->setCena(
                    $data['cena']
                );


                $product->setStatus('done');


                $this->em->flush();


                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );


                $this->redis->del(
                    "book_product:" . $product->getId()
                );


                $output->writeln('OK');

            }catch(\Exception $e){

                $product->setStatus('error');

                $this->em->flush();

                $output->writeln($e->getMessage());

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );

                continue;

            }


        }

        return Command::SUCCESS;
    }

}
