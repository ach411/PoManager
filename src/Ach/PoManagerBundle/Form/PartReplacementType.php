<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ach\PoManagerBundle\Entity\Product;

class PartReplacementType extends AbstractType
{
    private $productInstance;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'entity', array('class' => 'AchPoManagerBundle:Product', 'property' => 'shortDescription', 'multiple' => false, 'expanded' => false, 'choices' => $this->productInstance->getSpareParts(), 'empty_value' => 'select part that has been swapped'))
            ->add('oldPart', 'text', array('required' => false))
            ->add('newPart', 'text', array('required' => false))
            ->add('comment', 'textarea', array('required' => false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\PartReplacement'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_partreplacementtype';
    }

    public function __construct(\Ach\PoManagerBundle\Entity\Product $productInstance)
    {
        $this->productInstance = $productInstance;
    }

}
