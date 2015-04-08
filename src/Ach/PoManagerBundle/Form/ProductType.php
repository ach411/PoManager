<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pn')
            ->add('custPn')
            ->add('description')
            ->add('moq')
            ->add('active')
            ->add('comment')
            ->add('price')
            ->add('category')
            ->add('unit')
            ->add('coordinator')
            ->add('salesAdmin')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\Product'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_producttype';
    }
}
