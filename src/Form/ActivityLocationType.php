<?php

namespace App\Form;

use App\Entity\Embeddable\ActivityLocation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityLocationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('department', TextType::class, ['label' => 'Département', 'required' => false])
            ->add('commune', TextType::class, ['label' => 'Commune', 'required' => false])
            ->add('quarter', TextType::class, ['label' => 'Quartier', 'required' => false])
            ->add('marketZone', TextType::class, ['label' => 'Marché / zone d\'activité', 'required' => false])
            ->add('marketSpot', TextType::class, ['label' => 'Emplacement', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ActivityLocation::class]);
    }
}
