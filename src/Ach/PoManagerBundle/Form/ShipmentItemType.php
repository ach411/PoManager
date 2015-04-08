<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShipmentItemType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('qty',		'text')
			//->add('shipment')
			->add('poItem',		new PoItemType())
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Ach\PoManagerBundle\Entity\ShipmentItem'
		));
	}

	public function getName()
	{
		return 'ach_pomanagerbundle_shipmentitemtype';
	}
}
