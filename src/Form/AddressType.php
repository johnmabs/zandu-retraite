<?php

namespace App\Form;

use App\Entity\Embeddable\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('department', TextType::class, ['label' => 'Département', 'required' => false])
            ->add('commune', TextType::class, ['label' => 'Commune', 'required' => false])
            ->add('quarter', TextType::class, ['label' => 'Quartier', 'required' => false])
            ->add('street', TextType::class, ['label' => 'Rue', 'required' => false])
            ->add('number', TextType::class, ['label' => 'Numéro', 'required' => false])
            ->add('locality', TextType::class, ['label' => 'Localité', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Address::class]);
    }
}
