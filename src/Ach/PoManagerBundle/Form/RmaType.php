<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RmaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serialNumF', 'text')
            ->add('custSerialNum', 'text', array('required' => false))
            ->add('problemDescription', 'textarea', array('required' => true))
            ->add('repairLocation')
            ->add('comment', 'textarea', array('required' => false))
            ->add('contactEmail', 'email', array('required' => true))
            ->add('rpoFile', 'file', array('required' => false))
            ->add('custFile', 'file', array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\Rma'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_rmatype';
    }
}
