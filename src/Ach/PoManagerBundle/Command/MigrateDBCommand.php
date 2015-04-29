<?php

namespace Ach\PoManagerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Ach\PoManagerBundle\Entity\Revision;
use Ach\PoManagerBundle\Entity\Product;
use Ach\PoManagerBundle\Entity\Price;
use Ach\PoManagerBundle\Entity\Bpo;

class MigrateDBCommand extends ContainerAwareCommand
{
	protected function configure()
	{
		$this
			->setName('MigrateDB')
			->addArgument('message')
			->addArgument('db_user', InputArgument::OPTIONAL)
			->addArgument('db_pass', InputArgument::OPTIONAL)
			->setDescription('Migrate old DB to new DB')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		if($input->getArgument('message') == "erase")
		{
			$output->writeln("Erase Tables");
			$this->eraseData($input, $output);
		}
		
		if($input->getArgument('message') == "products")
		{
			$output->writeln("Migrate Products");
			$this->migrateProducts($input, $output);
		}
		
		if($input->getArgument('message') == "revisions")
		{
			$output->writeln("Migrate Revisions");
			$this->migrateRevisions($input, $output);
		}
		
		if($input->getArgument('message') == "Bpos")
		{
			$output->writeln("Migrate Bpos");
			$this->migrateBpo($input, $output);
		}
		
		if($input->getArgument('message') == "All")
		{
			$output->writeln("Migrate All");
			$this->migrateProducts($input, $output);
			$this->migrateRevisions($input, $output);
			$this->migrateBpo($input, $output);
		}
		
		$output->writeln('Migration executed');
	}
	
	private function eraseData(InputInterface $input, OutputInterface $output)
	{
		// connect to old database
		try
		{
				$bdd = new \PDO('mysql:host=localhost;dbname=po_manager', $input->getArgument('db_user'), $input->getArgument('db_pass'));
		}
		catch(Exception $e)
		{
			die('Erreur : '.$e->getMessage());
		}
		
		$req = $bdd->prepare('DELETE FROM ShipmentItem where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Shipment where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM PoItem where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Po where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Bpo where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Invoice where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Revision where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Product where 1;');
		$req->execute();
		
		$req = $bdd->prepare('DELETE FROM Price where 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE ShipmentItem AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Shipment AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE PoItem AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Po AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Bpo AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Invoice AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Revision AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Product AUTO_INCREMENT = 1;');
		$req->execute();
		
		$req = $bdd->prepare('ALTER TABLE Price AUTO_INCREMENT = 1;');
		$req->execute();
		
	}
	
	private function migrateProducts(InputInterface $input, OutputInterface $output)
	{
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
		
		// to begin, create 2 dummy products in the new database for Non Prod PO without P/N
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
				$bdd = new \PDO('mysql:host=localhost;dbname=stryker_po', $input->getArgument('db_user'), $input->getArgument('db_pass'));
		}
		catch(Exception $e)
		{
			die('Erreur : '.$e->getMessage());
		}
		
		$req = $bdd->prepare('SELECT vitec_index, sk_product_num, description, price1, price2, price3, price4, price5, currency, moq, active_price_index, comments, active, spare_part, hw_sw FROM product');
		$req->execute();
		
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
			
			$revisionInstance = new Revision();
			$revisionInstance->setRevision('unknown');
			$revisionInstance->setRevisionCust('unknown');
			$revisionInstance->setProduct($productInstance);
			$revisionInstance->setActive(false);
			
			$em->persist($productInstance);
			$em->persist($revisionInstance);
	
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
		
//			$output->writeln($data['sk_product_num'] . '->' . $skpn . 'VITEC P/N: ' . $data['vitec_index'] . ' price 1: ' . $price1 . ' price 2: ' . $price2 . ' price 3: ' . $price3);
//			$output->writeln($data['description']);
		}
		
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
	
	private function migrateRevisions(InputInterface $input, OutputInterface $output)
	{
		
		$em = $this->getContainer()->get('doctrine')->getManager();
		
		// get the Unit instance which will remain the same all along
		$repositoryProduct = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Product');
				
		//first create an N/A revision for non-prod product instance USD and EUR
		$noneUSDProductInstance = $repositoryProduct->findOneByPn('NoneUSD');
		$noneEURProductInstance = $repositoryProduct->findOneByPn('NoneEUR');
		$revisionNoneUSDInstance = new Revision();
		$revisionNoneEURInstance = new Revision();
		$revisionNoneUSDInstance->setProduct($noneUSDProductInstance);
		$revisionNoneEURInstance->setProduct($noneEURProductInstance);
		$revisionNoneUSDInstance->setActive(true);
		$revisionNoneEURInstance->setActive(true);
		$revisionNoneUSDInstance->setRevisionCust('N/A');
		$revisionNoneEURInstance->setRevisionCust('N/A');
		$revisionNoneUSDInstance->setRevision('N/A');
		$revisionNoneEURInstance->setRevision('N/A');
		
		$em->persist($revisionNoneUSDInstance);
		$em->persist($revisionNoneEURInstance);
		
		// for each product P/N create a default 'unknown' revision
		// on which PO with unknown revision of product item will point to
		$allProductInstances = $repositoryProduct->findAll();
		
		foreach($allProductInstances as $productInstance)
		{
			$revisionInstance = new Revision();
			
			$revisionInstance->setProduct($productInstance);
			$revisionInstance->setActive(false);
			$revisionInstance->setRevision('unknown');
			$revisionInstance->setRevisionCust('unknown');
			$revisionInstance->setComment('this dummy revision is made PO item we do not know the revision');
			
			$em->persist($revisionInstance);
		}
		
		$myfile = fopen("../../PoManager/revision_table.txt", "r");
		// for each line
		while(!feof($myfile))
		{
			//$file_line = fgets($myfile);
			//$cells = explode(';', $file_line);
			$cells = fgetcsv($myfile,1000,';');
			$index = 0;
			$rev = 'A';
			
			//remove empty string from the array
			//echo count($cells);
			$cells = array_filter($cells);
			//echo count($cells);
			
			while(isset($cells[$index]))
			{
				if($index == 0)
				{
					$output->write('P/N: '. $cells[$index]);
					$pn = $cells[$index];
					$productInstance = $repositoryProduct->findOneByPn($pn);
					$output->write(' ' . $productInstance->getDescription());
				}
				else
				{
					$output->write(iconv("Windows-1252","UTF-8",' - Revisions ' . $rev . ': ' . $cells[$index] . ' Drawing: '));
					$revisionInstance = new Revision();
					$revisionInstance->setProduct($productInstance);
					if($index == (count($cells)-1))
						$revisionInstance->setActive(true);
					else
						$revisionInstance->setActive(false);
					$revisionInstance->setRevisionCust($rev);
					$revisionInstance->setRevision('unknown');
					$revisionInstance->setComment(iconv("Windows-1252","UTF-8",$cells[$index]));
					$revisionInstance->setDrawingPath($productInstance->getCustPn() . $rev);
					$output->write($revisionInstance->getDrawingPath() . ' ');
					$rev = $this->incrementRev($rev);
					$output->write($revisionInstance->getProduct()->getDescription() . ($revisionInstance->getActive()? '*' : '%') . ' - ');
					$em->persist($revisionInstance);
					
				}
				
				
				$index++;
			}
			$output->writeln('');
			$output->writeln('=============================================');
			$output->writeln('');
		}
		fclose($myfile);
		$em->flush();
		//$output->writeln(getcwd());
	}
	
	private function incrementRev($revision)
	{
		if($revision == 'N')
			return 'P';
		if($revision == 'P')
			return 'R';
		if($revision == 'R')
			return 'T';
		
		return ++$revision;
		
	}
	
	
	private function migrateBpo(InputInterface $input, OutputInterface $output)
	{
		$em = $this->getContainer()->get('doctrine')->getManager();
		
		//open table BPO in old database and get entries
		
		// connect to old database
		try
		{
				$bdd = new \PDO('mysql:host=localhost;dbname=stryker_po', $input->getArgument('db_user'), $input->getArgument('db_pass'));
		}
		catch(Exception $e)
		{
			die('Erreur : '.$e->getMessage());
		}
		
		$req = $bdd->prepare('SELECT bpo.bpo_num, bpo.vitec_index, bpo.price_index, bpo.total_qty, bpo.effective_end_date, bpo.paired_bpo_num, bpo.pdf_path, bpo.comments, product.price1, product.price2, product.price3, product.price4, product.price5, product.currency FROM bpo inner join product on bpo.vitec_index = product.vitec_index');
		$req->execute();
		
		while($data = $req->fetch())
		{
			// create new instance of BPO for each entry in table BPO in old DB
			$bpoInstance = new Bpo();
			
			// set number
			$bpoInstance->setNum($data['bpo_num']);
			$output->writeln('Start processing BPO ' . $data['bpo_num'] . ' ...');
			
			// set revision
			$repositoryRevision = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Revision');
			
			$repositoryProduct = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Product');
				
			$instanceProduct = $repositoryProduct->findOneByPn($data['vitec_index']);
			$instanceRevision = $repositoryRevision->findOneBy(array('product' => $instanceProduct, 'revisionCust' => 'unknown'));
			
			$bpoInstance->setRevision($instanceRevision);
			
			// set price
			$repositoryPrice = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Price');
			
			$repositoryCurrency = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Currency');
			
			if($data['currency'] == 'dol')
				$currencyInstance = $repositoryCurrency->findOneByTla('USD');
			elseif($data['currency'] == 'eur')
				$currencyInstance = $repositoryCurrency->findOneByTla('EUR');
			
			switch($data['price_index']){
				case 1:
					$bpoInstance->setPrice($repositoryPrice->findOneBy(array('currency' => $currencyInstance, 'price' => $data['price1'])));
					break;
				case 2:
					$bpoInstance->setPrice($repositoryPrice->findOneBy(array('currency' => $currencyInstance, 'price' => $data['price2'])));
					break;
				case 3:
					$bpoInstance->setPrice($repositoryPrice->findOneBy(array('currency' => $currencyInstance, 'price' => $data['price3'])));
					break;
				case 4:
					$bpoInstance->setPrice($repositoryPrice->findOneBy(array('currency' => $currencyInstance, 'price' => $data['price4'])));
					break;
				case 5:
					$bpoInstance->setPrice($repositoryPrice->findOneBy(array('currency' => $currencyInstance, 'price' => $data['price5'])));
					break;
			}
			
			//set Quantity
			$bpoInstance->setQty($data['total_qty']);
			
			//set end date
			if(isset($data['effective_end_date']))
			{
			    $date = new \DateTime($data['effective_end_date']);
			    $bpoInstance->setEndDate($date);
			    //$output->write(gettype($data['effective_end_date']));
			}
			
			//set file path
			if(isset($data['pdf_path']))
			    $bpoInstance->setFilePath(str_replace('bpo_files/', '', $data['pdf_path']));
			
			//set comment
			$bpoInstance->setComment($data['comments']);
			
			$em->persist($bpoInstance);
		}
		
		$em->flush();
		
		// all bpo created, taking care of the pairing now
		
		$req2 = $bdd->prepare('SELECT * FROM bpo WHERE paired_bpo_num IS NOT NULL and paired_bpo_num != 0');
		$req2->execute();
		
		while($data = $req2->fetch())
		{
			$repositoryBpo = $this->getContainer()->get('doctrine')
				->getManager()
				->getRepository('AchPoManagerBundle:Bpo');
				
			$instanceBpo1 = $repositoryBpo->findOneByNum($data['bpo_num']);
			$instanceBpo2 = $repositoryBpo->findOneByNum($data['paired_bpo_num']);
			$instanceBpo1->setPairedBpo($instanceBpo2);
			
			$em->flush();
		}
	}

}