<?php

namespace CD\PlatformBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use CD\PlatformBundle\Form\Traduction_SourceType;

class Traduction_TargetType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('source',     Traduction_SourceType::class)
        ->add('target',     TextType::class)
        ->add('save',      SubmitType::class, array('label' => 'Ajouter'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CD\PlatformBundle\Entity\Traduction_Target'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cd_platformbundle_traduction_target';
    }


}
