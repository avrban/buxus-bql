<?php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BqlCommand extends Command {
    protected function configure()
    {
        $this->setName('app:bql')->setDescription('Vykona bql dopyt')
            ->addArgument('query', InputArgument::REQUIRED, 'Dopyt');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $query = $input->getArgument('query');

        $bql = new \Buxus\Bql\Main();
        $bql->execute($query);
    }
}