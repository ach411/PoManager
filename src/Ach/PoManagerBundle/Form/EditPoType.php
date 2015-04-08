<?php

namespace Ach\PoManagerBundle\Form;

//use Symfony\Component\Form\PoType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPoType extends PoType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		
		parent::buildForm($builder, $options) ;
		
		$builder
			->add('num',		'text', array('read_only' => true))
			->add('isBpo',		'checkbox', array('required' => false, 'read_only' => true) )
			->add('relNum',		'text', array('required' => false, 'read_only' => true) )
			->add('comment',		'text', array('required' => false, 'read_only' => true) )
			->add('buyerEmail',		'email', array('required' => false, 'read_only' => true) )
			->add('poItems',		'collection', array('type' => new PoItemType(),
										'allow_add' => true,
										'allow_delete' => true))
			//->add('totalAmount',	'money', array('currency' => false))
			//->add('file',		'file')
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
		return 'ach_pomanagerbundle_editpotype';
	}
}
