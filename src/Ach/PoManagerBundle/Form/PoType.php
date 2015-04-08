<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PoType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('num',		'text')
			->add('isBpo',		'checkbox', array('required' => false) )
			->add('relNum',		'text', array('required' => false) )
			->add('comment',		'text', array('required' => false) )
			->add('buyerEmail',		'email', array('required' => false) )
			->add('poItems',		'collection', array('type' => new PoItemType(),
										'allow_add' => true,
										'allow_delete' => true))
			->add('totalAmount',	'money', array('currency' => false))
			->add('file',		'file')
			//->add('poItems',		new PoItemType() )
		;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_class' => 'Ach\PoManagerBundle\Entity\Po'
		));
	}

	public function getName()
	{
		return 'ach_pomanagerbundle_potype';
	}
}
