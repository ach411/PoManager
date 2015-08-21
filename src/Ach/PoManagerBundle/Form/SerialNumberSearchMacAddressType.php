<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SerialNumberSearchMacAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('macAddress',				'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\SerialNumber'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_serialnumbersearchmacaddresstype';
    }

}