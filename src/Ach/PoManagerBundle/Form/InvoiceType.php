<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class InvoiceType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('num',		'text', array('read_only' => true))
			->add('invoiceDateF',		'text', array('read_only' => true))
			->add('comment',		'text', array('required' => false, 'read_only' => true) )
			//->add('shipmentItems',		'collection', array('type' => new ShipmentItemType(), 'read_only' => true))
			//->add('poItems',		'collection', array('type' => new PoItemType(),
			//							'allow_add' => true,
			//							'allow_delete' => true))
		//->add('totalAmount',	'money', array('currency' => false))
		//->add('file',		'file')
		//->add('poItems',		new PoItemType() )
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Ach\PoManagerBundle\Entity\Invoice'
		));
	}

	public function getName()
	{
		return 'ach_pomanagerbundle_invoicetype';
	}
}