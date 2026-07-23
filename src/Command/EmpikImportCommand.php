<?php

namespace App\Command;

use App\Entity\Empik;
use App\Resource\Pagination\Page;
use App\Service\EmpikListExtractor;
use App\Service\PageDownloader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'empik:import',
)]

class EmpikImportCommand extends Command
{

    public function __construct(private EntityManagerInterface $em,
                                private PageDownloader $downloader,
                                private EmpikListExtractor $extractor)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $page= new Page(1,100);

        while(true){
            $url=sprintf('https://www.empik.com/ksiazki?page=%d', $page->getPage());

            $output->writeln("Downloading: {$page->getPage()}");

            $html=$this->downloader->download($url);
            $links=$this->extractor->extract($html);

            if(count($links) === 0){
                $output->writeln("End of catalog");
                break;
            }

            foreach($links as $link){

                $exists=$this->em->getRepository(Empik::class)->findOneBy([
                    'url'=>$link,
                ]);

                if($exists){
                    continue;
                }

                $book = new Empik();
                $book->setUrl($link);
                $book->setShop('empik');
                $book->setStatus('pending');

                $this->em->persist($book);

            }

            $this->em->flush();
            $this->em->clear();

            $page = new Page(
                $page->getPage() + 1,
                $page->getLimit()
            );

            sleep(1);
        }

        return Command::SUCCESS;

    }


}
