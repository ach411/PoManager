<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PoItemSearchNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('num',		'text')
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
        return 'ach_pomanagerbundle_poitemsearchnumbertype';
    }
}
