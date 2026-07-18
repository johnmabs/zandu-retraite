<?php

namespace App\Form;

use App\Entity\Setting;
use App\Enum\ApiEnvironment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('defaultPensionRate', NumberType::class, [
                'label' => 'Taux retraite par défaut (%)',
                'constraints' => [new Assert\Range(min: 0, max: 100)],
            ])
            ->add('defaultManagementFeeRate', NumberType::class, [
                'label' => 'Taux frais de gestion par défaut (%)',
                'constraints' => [new Assert\Range(min: 0, max: 100)],
            ])
            ->add('defaultCnssRate', NumberType::class, [
                'label' => 'Taux CNSS par défaut (%)',
                'constraints' => [new Assert\Range(min: 0, max: 100)],
            ])
            ->add('registrationFeeAmount', NumberType::class, [
                'label' => 'Frais d\'inscription (FCFA)',
                'constraints' => [new Assert\PositiveOrZero()],
            ])
            ->add('mtnMomoNumber', TextType::class, ['label' => 'Numéro MTN MoMo', 'required' => false])
            ->add('airtelMoneyNumber', TextType::class, ['label' => 'Numéro Airtel Money', 'required' => false])
            ->add('bankName', TextType::class, ['label' => 'Nom de la banque', 'required' => false])
            ->add('bankIban', TextType::class, ['label' => 'IBAN / RIB', 'required' => false])
            ->add('mtnApiEnvironment', EnumType::class, ['class' => ApiEnvironment::class, 'label' => 'Environnement API MTN'])
            ->add('airtelApiEnvironment', EnumType::class, ['class' => ApiEnvironment::class, 'label' => 'Environnement API Airtel'])
            ->add('cnssApiEnvironment', EnumType::class, ['class' => ApiEnvironment::class, 'label' => 'Environnement API CNSS'])
            ->add('notifyAdminByEmail', CheckboxType::class, ['label' => 'Notifier les admins par email', 'required' => false])
            ->add('notifyAdminBySms', CheckboxType::class, ['label' => 'Notifier les admins par SMS', 'required' => false])
            ->add('notifyAdminByWhatsapp', CheckboxType::class, ['label' => 'Notifier les admins par WhatsApp', 'required' => false])
            // Seuils de palier — non mappés à l'entité directement, reconstruits en contrôleur
            ->add('silverMin', IntegerType::class, ['label' => 'Seuil palier Argent (FCFA/jour)', 'mapped' => false, 'constraints' => [new Assert\PositiveOrZero()]])
            ->add('goldMin', IntegerType::class, ['label' => 'Seuil palier Or (FCFA/jour)', 'mapped' => false, 'constraints' => [new Assert\PositiveOrZero()]])
            ->add('platinumMin', IntegerType::class, ['label' => 'Seuil palier Platine (FCFA/jour)', 'mapped' => false, 'constraints' => [new Assert\PositiveOrZero()]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Setting::class]);
    }
}
