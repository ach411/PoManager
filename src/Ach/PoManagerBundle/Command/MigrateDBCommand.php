<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ach\PoManagerBundle\Entity\Product;

class MigrateDBCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('MigrateDB')
	    ->addArgument('message')
//	    ->addArgument('host')
//	    ->addArgument('baseUrl')
            ->setDescription('Migrate old DB to new DB')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $context = $this->getContainer()->get('router')->getContext();
//        $context->setHost($input->getArgument('host'));
//        $context->setScheme('http');
//        $context->setBaseUrl($input->getArgument('baseUrl'));

	// connect to old database
	try
	{
            $bdd = new \PDO('mysql:host=localhost;dbname=stryker_po', 'db_login', 'db_password');
	}
	catch(Exception $e)
	{
	    die('Erreur : '.$e->getMessage());
	}

	$req = $bdd->prepare('SELECT vitec_index, sk_product_num, description, price1, price2, price3, price4, price5, currency, moq, active_price_index, comments, active FROM product');
	$req->execute();

	$log = null;

	while($data = $req->fetch())
	{
	    $skpn = $data['sk_product_num'];
	    $skpn = preg_replace("/(\d{3})-(\d{3})-(\d{3})/", "0$1$2$3", $skpn);
	    $skpn = preg_replace("/^(\d{4})-(\d{3})-(\d{3})/", "$1$2$3", $skpn);
	    $skpn = preg_replace("/^(\d{9})\z/", "0$1", $skpn);
	    $skpn = preg_replace("/\(not a production\)/", "N/A", $skpn);

	    if(!is_null($data['price1']))
	    {
		$price1 = $data['price1'];
	    }

	    if(!is_null($data['price2']))
	    {
		$price2 = $data['price2'];
	    }

	    if(!is_null($data['price3']))
	    {
		$price3 = $data['price3'];
	    }

	    $output->writeln($data['sk_product_num'] . '->' . $skpn . 'VITEC P/N: ' . $data['vitec_index'] . ' price 1: ' . $price1 . ' price 2: ' . $price2 . ' price 3: ' . $price3);
//	    $output->writeln($data['description']);
	}

		
	$repositoryNotification = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Notification');

	$listNotifications = $repositoryNotification->findAll();

	$em = $this->getContainer()->get('doctrine')->getManager();
	

	//$log .= $input->getArgument('message');

	$product = new Product();

	

	// scan all the pending notification of the table and send message for each
	//foreach($listNotifications as $notification)
	//{
	//    $log = $log.$this->getContainer()->get('ach_po_manager.send_notification')->sendNotification($notification);
	//    $em->remove($notification);
	//}

	//$em->flush();

	
        $output->writeln('Migration executed: ' . $log);
    }
}