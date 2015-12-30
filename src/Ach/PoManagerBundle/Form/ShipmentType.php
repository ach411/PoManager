<?php

namespace Ach\PoManagerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ShipmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('trackingNum', 'text')
            ->add('carrier', 'entity', array('class' => 'AchPoManagerBundle:Carrier', 'property' => 'name', 'multiple' => false, 'expanded' => false ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Ach\PoManagerBundle\Entity\Shipment'
        ));
    }

    public function getName()
    {
        return 'ach_pomanagerbundle_shipmenttype';
    }
}
