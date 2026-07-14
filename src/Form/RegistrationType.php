<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Sector;
use App\Entity\SubSector;
use App\Enum\EngagementDuration;
use App\Enum\Gender;
use App\Enum\SavingsGoal;
use App\Repository\SubSectorRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use SymfonyCasts\DynamicForms\DependentField;
use Symfonycasts\DynamicForms\DynamicFormBuilder;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Enrobe le builder pour pouvoir déclarer des champs dépendants (addDependent)
        $builder = new DynamicFormBuilder($builder);

        $builder
            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('gender', EnumType::class, [
                'class' => Gender::class,
                'label' => 'Sexe',
                'expanded' => true,
                'required' => false,
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('phone', TelType::class, ['label' => 'Numéro de téléphone'])
            ->add('whatsappPhone', TelType::class, ['label' => 'Numéro WhatsApp', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('idDocumentNumber', TextType::class, ['label' => 'N° pièce d\'identité', 'required' => false])
            ->add('profession', TextType::class, ['label' => 'Profession', 'required' => false])
            ->add('sector', EntityType::class, [
                'class' => Sector::class,
                'choice_label' => 'name',
                'label' => 'Secteur d\'activité',
                'placeholder' => '-- Sélectionner --',
            ])
            // Se reconstruit côté serveur à chaque changement de "sector" (via ajax du
            // Live Component), avec uniquement les sous-secteurs du secteur choisi.
            ->addDependent('subSector', 'sector', function (DependentField $field, ?Sector $sector) {
                $field->add(EntityType::class, [
                    'class' => SubSector::class,
                    'choice_label' => 'name',
                    'label' => 'Sous-secteur',
                    'required' => false,
                    'placeholder' => null === $sector ? 'Sélectionnez d\'abord un secteur' : '-- Sélectionner --',
                    'disabled' => null === $sector,
                    'query_builder' => function (SubSectorRepository $repo) use ($sector): QueryBuilder {
                        $qb = $repo->createQueryBuilder('sub')->orderBy('sub.name', 'ASC');

                        // Aucun secteur choisi : liste volontairement vide plutôt que
                        // tous les sous-secteurs, pour ne rien pré-sélectionner de faux
                        return $sector
                            ? $qb->andWhere('sub.sector = :sector')->setParameter('sector', $sector)
                            : $qb->andWhere('1 = 0');
                    },
                ]);
            })
            ->add('customSectorLabel', TextType::class, [
                'label' => 'Précisez si "Autre"',
                'required' => false,
            ])
            ->add('homeAddress', AddressType::class, ['label' => false])
            ->add('activityLocation', ActivityLocationType::class, ['label' => false])
            ->add('beneficiary', BeneficiaryType::class, ['label' => false])
            ->add('dailyPaymentAmount', NumberType::class, [
                'label' => 'Versement journalier souhaité (FCFA)',
                'html5' => true,
                'constraints' => [new Assert\GreaterThanOrEqual(500)],
            ])
            ->add('engagementDuration', EnumType::class, [
                'class' => EngagementDuration::class,
                'label' => 'Durée d\'engagement',
                'placeholder' => '-- Sélectionner --',
                'required' => false,
            ])
            ->add('savingsGoal', EnumType::class, [
                'class' => SavingsGoal::class,
                'label' => 'Objectif d\'épargne',
                'placeholder' => '-- Sélectionner --',
                'required' => false,
            ])
            ->add('goalDetails', TextType::class, [
                'label' => 'Précisez votre objectif',
                'required' => false,
            ])
            ->add('pin', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'Code PIN (4 chiffres)'],
                'second_options' => ['label' => 'Confirmez le PIN'],
                'invalid_message' => 'Les deux codes PIN ne correspondent pas.',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^\d{4}$/', message: 'Le PIN doit contenir exactement 4 chiffres.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Member::class]);
    }
}
