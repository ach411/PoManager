<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ach\PoManagerBundle\Entity\Product;
use Ach\PoManagerBundle\Entity\Price;

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

	// get the entity manager
	$em = $this->getContainer()->get('doctrine')->getManager();
	
	// get the Unit instance which will remain the same all along
	$repositoryUnit = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Unit');
	$unitInstance = $repositoryUnit->find(1);
	
	// get the customer instance which will remain the same all along
	$repositoryCustomer = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Customer');
	$customerInstance = $repositoryCustomer->find(1);
	
	// get the billing manager instance which will remain the same all along
	$repositoryBillingManager = $this->getContainer()->get('doctrine')
                    ->getManager()
                    ->getRepository('AchPoManagerBundle:BillingManager');
    $billingManagerInstance = $repositoryBillingManager->find(1);
	
	// get the prod and the shipping manager repo
	$repositoryProdManager = $this->getContainer()->get('doctrine')
                    ->getManager()
                    ->getRepository('AchPoManagerBundle:ProdManager');
		
	$repositoryShippingManager = $this->getContainer()->get('doctrine')
                    ->getManager()
                    ->getRepository('AchPoManagerBundle:ShippingManager');
					
	
	
	
	// to begin, create 2 dummy products in the new database
	$productInstanceUSD = new Product();
	$productInstanceUSD->setPn('NoneUSD');
	$productInstanceUSD->setCustPn('NoneUSD');
	$productInstanceUSD->setMoq(1);
	$productInstanceUSD->setActive(true);
	$this->registerPrice(0, true, 'dol', $productInstanceUSD, $em);
	$productInstanceUSD->setUnit($unitInstance);
	$productInstanceUSD->setCustomer($customerInstance);
	$productInstanceUSD->setdescription('NON-PROD ITEM. PLEASE PROVIDE DESCRIPTION IN COMMENT FIELD.');
	$productInstanceUSD->setBillingManager($billingManagerInstance);
	$productInstanceUSD->setProdManager($repositoryProdManager->find(3));
	$productInstanceUSD->setShippingManager($repositoryShippingManager->find(3));
	
	$em->persist($productInstanceUSD);
	
	$productInstanceEUR = new Product();
	$productInstanceEUR->setPn('NoneEUR');
	$productInstanceEUR->setCustPn('NoneEUR');
	$productInstanceEUR->setMoq(1);
	$productInstanceEUR->setActive(true);
	$this->registerPrice(0, true, 'eur', $productInstanceEUR, $em);
	$productInstanceEUR->setUnit($unitInstance);
	$productInstanceEUR->setCustomer($customerInstance);
	$productInstanceEUR->setdescription('NON-PROD ITEM. PLEASE PROVIDE DESCRIPTION IN COMMENT FIELD.');
	$productInstanceEUR->setBillingManager($billingManagerInstance);
	$productInstanceEUR->setProdManager($repositoryProdManager->find(3));
	$productInstanceEUR->setShippingManager($repositoryShippingManager->find(3));
	
	$em->persist($productInstanceEUR);
	
	// connect to old database
	try
	{
            $bdd = new \PDO('mysql:host=localhost;dbname=stryker_po', 'vitec', 'chatillon92320');
	}
	catch(Exception $e)
	{
	    die('Erreur : '.$e->getMessage());
	}

	$req = $bdd->prepare('SELECT vitec_index, sk_product_num, description, price1, price2, price3, price4, price5, currency, moq, active_price_index, comments, active, spare_part, hw_sw FROM product');
	$req->execute();

	$log = null;


	while($data = $req->fetch())
	{
	    // create the product instance
	    $productInstance = new Product();

	    $skpn = $data['sk_product_num'];
	    $skpn = preg_replace("/(\d{3})-(\d{3})-(\d{3})/", "0$1$2$3", $skpn);
	    $skpn = preg_replace("/^(\d{4})-(\d{3})-(\d{3})/", "$1$2$3", $skpn);
	    $skpn = preg_replace("/^(\d{9})\z/", "0$1", $skpn);
	    $custPn = preg_replace("/\(not a production\)/", "N/A", $skpn);
		
		if(stripos($data['description'], 'AMPSK'))
		{
			$prodManagerInstance = $repositoryProdManager->find(2);
			$shippingManagerInstance = $repositoryShippingManager->find(2);
		}
		else
		{
			$prodManagerInstance = $repositoryProdManager->find(1);
			$shippingManagerInstance = $repositoryShippingManager->find(1);
		}

		
		$repositoryCategory = $this->getContainer()->get('doctrine')
                    ->getManager()
                    ->getRepository('AchPoManagerBundle:Category');
		
		if($data['spare_part'] == 'Y' or $data['spare_part'] == 'y')
		{
			$categoryInstance1 = $repositoryCategory->findOneByName('Spare Part');
		}
		else
		{
			$categoryInstance1 = $repositoryCategory->findOneByName('Finished Good');
		}
		
		if($data['hw_sw'] == 'h' or $data['hw_sw'] == 'H')
		{
			$categoryInstance2 = $repositoryCategory->findOneByName('Hardware');
		}
		else
		{
			$categoryInstance2 = $repositoryCategory->findOneByName('Software');
		}

	    $productInstance->setCustPn($custPn);
	    $productInstance->setPn($data['vitec_index']);
	    $productInstance->setDescription($data['description']);
	    $productInstance->setUnit($unitInstance);
	    $productInstance->setMoq($data['moq']);
	    $productInstance->setComment($data['comments']);
	    $productInstance->setActive($data['active'] == 'Y' ? true : false);
	    $productInstance->setCustomer($customerInstance);
	    $productInstance->setBillingManager($billingManagerInstance);
	    $productInstance->setProdManager($prodManagerInstance);
	    $productInstance->setShippingManager($shippingManagerInstance);
	    $productInstance->addCategory($categoryInstance1);
		$productInstance->addCategory($categoryInstance2);
	    

	    $this->registerPrice($data['price1'], $data['active_price_index'] == 1, $data['currency'], $productInstance, $em);
	    $this->registerPrice($data['price2'], $data['active_price_index'] == 2, $data['currency'], $productInstance, $em);
	    $this->registerPrice($data['price3'], $data['active_price_index'] == 3, $data['currency'], $productInstance, $em);
	    $this->registerPrice($data['price4'], $data['active_price_index'] == 4, $data['currency'], $productInstance, $em);
	    $this->registerPrice($data['price5'], $data['active_price_index'] == 5, $data['currency'], $productInstance, $em);

	    $em->persist($productInstance);

	    $em->flush();

	    //$output->writeln($productInstance->getPn());
	    //$output->writeln($productInstance->getCustPn());
	    //$output->writeln($productInstance->getDescription());
	    //$output->writeln($productInstance->getPrice()->getPrice() . ' ' . $productInstance->getPrice()->getCurrency()->getTla());
	    //$output->writeln($productInstance->getUnit()->getName());
	    //$output->writeln($productInstance->getMoq());
	    //$output->writeln($productInstance->getComment());
	    //$output->writeln('====================');

	    $output->write('.');

//	    $output->writeln($data['sk_product_num'] . '->' . $skpn . 'VITEC P/N: ' . $data['vitec_index'] . ' price 1: ' . $price1 . ' price 2: ' . $price2 . ' price 3: ' . $price3);
//	    $output->writeln($data['description']);
	}
	

	//$log .= $input->getArgument('message');

	
        $output->writeln('Migration executed: ' . $log);
    }

    // check if price already exists in database 
    private function registerPrice($price, $active_price, $currency, $productInstance, $em)
    {
	if(is_null($price))
	    return;

	$repositoryPrice = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Price');

	$priceInstance = $repositoryPrice->findOneByPrice($price);
	
	// create the price if it does exist yet
	if(is_null($priceInstance) or ($priceInstance->getCurrency()->getTla() == 'USD' and $currency == 'eur') or ($priceInstance->getCurrency()->getTla() == 'EUR' and $currency == 'dol'))
	{
	    $repositoryCurrency = $this->getContainer()->get('doctrine')
		    ->getManager()
		    ->getRepository('AchPoManagerBundle:Currency');
	    
	    if($currency == 'dol')
	        $instanceCurrency = $repositoryCurrency->findOneByTla('USD');

	    if($currency == 'eur')
	        $instanceCurrency = $repositoryCurrency->findOneByTla('EUR');

	    $priceInstance = new Price();
	    $priceInstance->setPrice($price);
	    $priceInstance->setCurrency($instanceCurrency);

	    $em->persist($priceInstance);
	    
	}
	
	if($active_price)
	{
	    $productInstance->setPrice($priceInstance);
	}
	
	//return $productInstance;
	
    }

}