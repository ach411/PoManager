<?php

namespace Ach\PoManagerBundle\Form;

//use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditPoItemType extends PoItemType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
		parent::buildForm($builder, $options) ;
		
		$builder->add('lineNum',		'integer', array('read_only' => true));
		/*
        $builder
            ->add('lineNum',		'integer')
            ->add('description',	'text', array('read_only' => true) )
            ->add('qty',		'integer')
//            ->add('dueDate',		'date', array('widget' => 'text'))
            ->add('dueDate',		'date')
	    ->add('pnF',		'text')
//	    ->add('custPnF',		'text', array('read_only' => true))
	    ->add('custPnF',		'text')
            ->add('revisionF',		'text')
            ->add('priceF',		'money', array('read_only' => true, 'currency' => false))
            ->add('totalPriceF',	'money', array('read_only' => true, 'currency' => false))
            ->add('comment',		'text', array('required' => false))
	    ->add('historyF',		'text', array('read_only' => true))
        ;*/
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\PoItem'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_editpoitemtype';
    }
}
