<?php

namespace App\Twig\Components;

use App\Entity\Member;
use App\Form\RegistrationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * Enrobe RegistrationType pour bénéficier de la réactivité (rechargement du
 * sous-secteur au changement de secteur) sans écrire de JS. La soumission
 * réelle du formulaire reste un POST HTML classique vers la route du
 * contrôleur d'inscription — ce composant ne gère que le rendu interactif,
 * pas la persistance (voir MemberRegistrationService pour l'orchestration).
 */
#[AsLiveComponent('RegistrationForm', template: 'components/RegistrationForm.html.twig')]
final class RegistrationFormComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(RegistrationType::class, new Member());
    }
}
