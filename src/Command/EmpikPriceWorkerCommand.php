<?php

namespace App\Command;

use App\Entity\Empik;
use App\Service\EmpikExtractor;
use App\Service\PageDownloader;
use App\Service\PriceHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'empik:price-worker',
    description: 'Update Empik prices'
)]
class EmpikPriceWorkerCommand extends Command
{
    private string $queue = 'empik:price:queue';
    private string $processed = 'empik:price:processed';


    public function __construct(
        private EntityManagerInterface $em,
        private Client $redis,
        private PageDownloader $downloader,
        private EmpikExtractor $extractor,
        private PriceHistoryService $priceHistory
    ) {
        parent::__construct();
    }


    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {

        while (true) {

            $item = $this->redis->brpoplpush(
                $this->queue,
                $this->processed,
                5
            );


            if (!$item) {
                continue;
            }


            try {

                $data = json_decode($item, true);


                $product = $this->em
                    ->getRepository(Empik::class)
                    ->find($data['id']);


                if (!$product) {

                    $this->redis->lrem(
                        $this->processed,
                        1,
                        $item
                    );

                    continue;
                }


                $output->writeln(
                    "Updating price: ".$product->getUrl()
                );


                $html = $this->downloader->download(
                    $product->getUrl()
                );


                $result = $this->extractor->extract($html);


                $newPrice = $result['cena'] ?? null;


                if ($newPrice !== null) {

                    $product->setCena(
                        $newPrice
                    );


                    $this->priceHistory->updateEmpikPrice(
                        $product,
                        $newPrice
                    );
                }


                $this->em->flush();


                $this->redis->lrem(
                    $this->processed,
                    1,
                    $item
                );


                $this->redis->del(
                    'empik:price:' . $product->getId()
                );


                $output->writeln(
                    "<info>Updated {$product->getId()}</info>"
                );


            } catch (\Throwable $e) {


                $output->writeln(
                    "<error>{$e->getMessage()}</error>"
                );


                if(isset($data['id'])) {

                    $this->redis->del(
                        'empik:price:' . $data['id']
                    );
                }


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
