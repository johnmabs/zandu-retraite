<?php

namespace App\Form\Wizard;

use App\Enum\RegistrationFeeMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationStep3Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('registrationFeePaymentMethod', EnumType::class, [
                'class' => RegistrationFeeMethod::class,
                'label' => 'Moyen de paiement prévu',
                'expanded' => true,
                'constraints' => [new Assert\NotNull(message: 'Merci de choisir un moyen de paiement.')],
                'attr' => ['data-action' => 'change->registration-fee-method#refresh'],
            ])
            ->add('registrationFeeSenderPhone', TelType::class, [
                'label' => 'Votre numéro pour ce paiement',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
