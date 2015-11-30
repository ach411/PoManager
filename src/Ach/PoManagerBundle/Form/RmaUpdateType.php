<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Ach\PoManagerBundle\Entity\Product;

class RmaUpdateType extends AbstractType
{
    private $productInstance;
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serialNumF', 'text', array('read_only' => true))
            ->add('custSerialNum', 'text', array('required' => false, 'read_only' => true))
            ->add('problemDescription', 'textarea', array('required' => true, 'read_only' => true))
            ->add('creationDate', 'date', array('widget' => 'single_text', 'read_only' => true))
            ->add('receptionDate', 'date', array('widget' => 'single_text', 'read_only' => true))
            ->add('investigationResult', 'textarea', array('required' => true))
            ->add('comment', 'textarea', array('required' => false))
            ->add('contactEmail', 'email', array('required' => true, 'read_only' => true))
            ->add('problemCategory', null, array('label' => 'Choose Problem Category', 'empty_value' => 'choose category', 'required' => true))
            ->add('partReplacements', 'collection', array(
                'type' => new PartReplacementType($this->productInstance),
                'allow_add' => true,
                'allow_delete' => true ))
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
        return 'ach_pomanagerbundle_rmaupdatetype';
    }

    public function __construct(\Ach\PoManagerBundle\Entity\Product $productInstance)
    {
        $this->productInstance = $productInstance;
    }
}

    