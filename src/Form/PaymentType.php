<?php

namespace App\Form;

use App\Entity\Payment;
use App\Enum\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', NumberType::class, [
                'label' => 'Montant (FCFA)',
                'html5' => true,
                'constraints' => [new Assert\Positive()],
            ])
            ->add('paymentDate', DateType::class, [
                'label' => 'Date du versement',
                'widget' => 'single_text',
                'constraints' => [new Assert\LessThanOrEqual('today', message: 'La date ne peut pas être dans le futur.')],
            ])
            ->add('paymentMethod', EnumType::class, [
                'class' => PaymentMethod::class,
                'label' => 'Moyen de paiement',
                'choices' => [PaymentMethod::MtnMomo, PaymentMethod::AirtelMoney, PaymentMethod::BankTransfer],
                'expanded' => true,
            ])
            ->add('senderPhoneNumber', TelType::class, [
                'label' => 'Numéro utilisé pour le paiement',
                'required' => false,
                'row_attr' => ['data-payment-fields-target' => 'phoneRow'],
            ])
            ->add('externalReference', TextType::class, [
                'label' => 'Référence du virement',
                'required' => false,
                'row_attr' => ['data-payment-fields-target' => 'referenceRow'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Payment::class]);
    }
}
