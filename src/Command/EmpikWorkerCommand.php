<?php

namespace App\Command;

use App\Entity\Empik;
use App\Service\EmpikExtractor;
use App\Service\PageDownloader;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(
    name: 'empik:worker',
    description: 'Process books from queue'
)]

class EmpikWorkerCommand extends Command
{
    private string $queue = 'book_product_queue';
    private string $processed = 'book_product_processed';

    public function __construct(private EntityManagerInterface $em,
                                   private Client $redis,
                                   private PageDownloader $downloader,
                                   private EmpikExtractor $extractor )
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

            if(!$item){
                continue;
            }

         $data=json_decode($item,true);

            $product=$this->em->getRepository(Empik::class)->find($data['id']);

            if(!$product){

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );
                continue;
            }


            try{
                $output->writeln('Product download ' .$product->getUrl());

                $product->setStatus("processing");

                $html=$this->downloader->download($data['url']);
                file_put_contents('/tmp/empik.html', $html);

                $data=$this->extractor->extract($html);

                $product->setTytul(
                    $data['tytul'] ?? 'Brak tytułu'
                );

                $product->setAutor(
                    $data['autor'] ?? null
                );

                $product->setWydawnictwo(
                    $data['wydawnictwo'] ?? null
                );

                $product->setRokWydania(
                    $data['rok_wydania'] ?? null
                );

                $product->setCena(
                    $data['cena'] ?? null
                );


                $product->setStatus('done');


                $this->em->flush();

                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );


                $this->redis->del("book-product" . $product->getId());
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
