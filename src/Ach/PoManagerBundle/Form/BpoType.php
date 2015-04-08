<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BpoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num',			'text')
            ->add('qty',			'integer')
            ->add('priceF',			'money', array('disabled' => true, 'currency' => false))
            ->add('descriptionF',	'text', array('disabled' => true))
            ->add('revisionF',		'text')
            ->add('startDate',		'date', array('required' => false))
            ->add('endDate',		'date', array('required' => false))
            ->add('file',			'file')
            ->add('comment',		'text', array('required' => false))
            ->add('buyerEmail',		'email')
            //->add('pairedBpo')
            //->add('revisionF',		'hidden')
            //->add('price')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\Bpo'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_bpotype';
    }
}
