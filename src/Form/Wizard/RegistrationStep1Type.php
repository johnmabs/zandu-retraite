<?php

namespace App\Form\Wizard;

use App\Entity\Sector;
use App\Entity\SubSector;
use App\Enum\Gender;
use App\Form\ActivityLocationType;
use App\Form\AddressType;
use App\Form\BeneficiaryType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationStep1Type extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom', 'constraints' => [new Assert\NotBlank()]])
            ->add('lastName', TextType::class, ['label' => 'Nom', 'constraints' => [new Assert\NotBlank()]])
            ->add('gender', EnumType::class, ['class' => Gender::class, 'label' => 'Sexe', 'expanded' => true, 'required' => false])
            ->add('birthDate', DateType::class, ['label' => 'Date de naissance', 'widget' => 'single_text', 'required' => false])
            ->add('phone', TelType::class, ['label' => 'Téléphone', 'constraints' => [new Assert\NotBlank()]])
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('idDocumentNumber', TextType::class, ['label' => 'N° pièce d\'identité', 'required' => false])
            ->add('profession', TextType::class, ['label' => 'Profession', 'required' => false])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur d\'activité',
                'placeholder' => '-- Sélectionner --',
                'constraints' => [new Assert\NotNull(message: 'Le secteur d\'activité est obligatoire.')],
                'attr' => ['data-action' => 'sector-cascade#update', 'data-sector-cascade-target' => 'sector'],
            ])
            ->add('subSector', EntityType::class, [
                'class' => SubSector::class,
                'choice_label' => 'name',
                'group_by' => 'sector.name',
                'label' => 'Sous-secteur',
                'required' => false,
                'attr' => ['data-sector-cascade-target' => 'subSector'],
            ])
            ->add('customSectorLabel', TextType::class, [
                'label' => 'Précisez votre secteur d\'activité',
                'required' => false,
                'attr' => ['data-sector-cascade-target' => 'customLabel'],
                'row_attr' => ['data-sector-cascade-target' => 'customLabelRow'],
            ])
            ->add('whatsappPhone', TelType::class, ['label' => 'Numéro WhatsApp', 'required' => false])
            ->add('beneficiary', BeneficiaryType::class, ['label' => false])
            ->add('activityLocation', ActivityLocationType::class, ['label' => false])
            ->add('homeAddress', AddressType::class, ['label' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => null]);
    }
}
