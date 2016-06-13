<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncWithProdDatabaseCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('SyncWithProdDatabase')
            ->addArgument('systemName')
            ->setDescription('Synchronize PoManager with Production Database')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start synchro with production database...');

        $log = $this->getContainer()->get('ach_po_manager.sync_prod_database')->syncShipmentBatch($input->getArgument('systemName'));

        $output->writeln($log . "end of synchro");

    }
}