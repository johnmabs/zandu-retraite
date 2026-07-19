<?php

namespace App\Form\Wizard;

use App\Enum\EngagementDuration;
use App\Enum\SavingsGoal;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationStep2Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dailyPaymentAmount', NumberType::class, [
                'label' => 'Versement journalier (FCFA)',
                'html5' => true,
                'constraints' => [new Assert\NotBlank(), new Assert\GreaterThanOrEqual(500)],
                'attr' => ['min' => 500, 'step' => 100, 'data-registration-plan-target' => 'dailyAmount', 'data-action' => 'input->registration-plan#recalculate'],
            ])
            ->add('engagementDuration', EnumType::class, [
                'class' => EngagementDuration::class,
                'label' => 'Durée de cotisation',
                'placeholder' => '-- Sélectionner --',
                'constraints' => [new Assert\NotNull(message: 'La durée d\'engagement est obligatoire.')],
                'attr' => ['data-registration-plan-target' => 'duration', 'data-action' => 'change->registration-plan#recalculate'],
            ])
            ->add('savingsGoal', EnumType::class, [
                'class' => SavingsGoal::class,
                'label' => 'Objectif de retraite',
                'placeholder' => '-- Sélectionner --',
                'required' => false,
            ])
            ->add('goalDetails', TextType::class, ['label' => 'Précisez votre objectif', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
