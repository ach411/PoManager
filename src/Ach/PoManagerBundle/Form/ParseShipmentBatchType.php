<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ParseShipmentBatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('file',	'file')
            ->add('comment', 'text', array('required' => false) )
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\ShipmentBatch'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_parseshipmentbatchtype';
    }
}
