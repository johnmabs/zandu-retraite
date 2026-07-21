<?php

namespace App\Controller\Web\Member;

use App\Entity\Embeddable\ActivityLocation;
use App\Entity\Embeddable\Address;
use App\Entity\Embeddable\Beneficiary;
use App\Entity\Member;
use App\Form\Wizard\RegistrationStep1Type;
use App\Form\Wizard\RegistrationStep2Type;
use App\Form\Wizard\RegistrationStep3Type;
use App\Repository\SectorRepository;
use App\Repository\SettingRepository;
use App\Repository\SubSectorRepository;
use App\Service\MemberRegistrationService;
use App\Service\RegistrationWizard\RegistrationWizardSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/member-area/registration')]
class RegistrationWizardController extends AbstractController
{
    #[Route('/step/1', name: 'member_registration_step1')]
    public function step1(Request $request, RegistrationWizardSession $wizard, SectorRepository $sectorRepository, SubSectorRepository $subSectorRepository): Response
    {
        $stored = $wizard->get('step1') ?? [];
        $data = [
            'firstName' => $stored['firstName'] ?? null,
            'lastName' => $stored['lastName'] ?? null,
            'gender' => $stored['gender'] ?? null,
            'birthDate' => $stored['birthDate'] ?? null,
            'phone' => $stored['phone'] ?? null,
            'email' => $stored['email'] ?? null,
            'idDocumentNumber' => $stored['idDocumentNumber'] ?? null,
            'profession' => $stored['profession'] ?? null,
            'sector' => isset($stored['sectorId']) ? $sectorRepository->find($stored['sectorId']) : null,
            'subSector' => isset($stored['subSectorId']) ? $subSectorRepository->find($stored['subSectorId']) : null,
            'customSectorLabel' => $stored['customSectorLabel'] ?? null,
            'whatsappPhone' => $stored['whatsappPhone'] ?? null,
            'beneficiary' => $stored['beneficiary'] ?? new Beneficiary(),
            'activityLocation' => $stored['activityLocation'] ?? new ActivityLocation(),
            'homeAddress' => $stored['homeAddress'] ?? new Address(),
        ];

        $form = $this->createForm(RegistrationStep1Type::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = $form->getData();

            $wizard->set('step1', [
                'firstName' => $submitted['firstName'],
                'lastName' => $submitted['lastName'],
                'gender' => $submitted['gender'],
                'birthDate' => $submitted['birthDate'],
                'phone' => $submitted['phone'],
                'email' => $submitted['email'],
                'idDocumentNumber' => $submitted['idDocumentNumber'],
                'profession' => $submitted['profession'],
                'sectorId' => $submitted['sector']?->getId(),
                'subSectorId' => $submitted['subSector']?->getId(),
                'customSectorLabel' => $submitted['customSectorLabel'],
                'whatsappPhone' => $submitted['whatsappPhone'],
                'beneficiary' => $submitted['beneficiary'],
                'activityLocation' => $submitted['activityLocation'],
                'homeAddress' => $submitted['homeAddress'],
            ]);

            return $this->redirectToRoute('member_registration_step2');
        }

        $sectors = $sectorRepository->findAllWithSubSectors();

        $subSectorsBySector = [];
        $otherSectorIds = [];

        foreach ($sectors as $sector) {
            $subSectorsBySector[(string) $sector->getId()] = array_map(
                fn($sub) => ['id' => (string) $sub->getId(), 'name' => $sub->getName()],
                $sector->getSubSectors()->toArray(),
            );

            if ($sector->isOther()) {
                $otherSectorIds[] = (string) $sector->getId();
            }
        }

        return $this->render('member/registration_wizard/step1.html.twig', [
            'form' => $form,
            'sectors' => $sectors,
            'subSectorsBySector' => $subSectorsBySector,
            'otherSectorIds' => $otherSectorIds,
        ]);
    }

    #[Route('/step/2', name: 'member_registration_step2')]
    public function step2(Request $request, RegistrationWizardSession $wizard, SettingRepository $settingRepository): Response
    {
        if (!$wizard->hasStep('step1')) {
            return $this->redirectToRoute('member_registration_step1');
        }

        $form = $this->createForm(RegistrationStep2Type::class, $wizard->get('step2') ?? []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wizard->set('step2', $form->getData());

            return $this->redirectToRoute('member_registration_step3');
        }

        $setting = $settingRepository->getOrCreate();

        return $this->render('member/registration_wizard/step2.html.twig', [
            'form' => $form,
            'rates' => [
                'pension' => $setting->getDefaultPensionRate(),
                'management' => $setting->getDefaultManagementFeeRate(),
                'cnss' => $setting->getDefaultCnssRate(),
            ],
        ]);
    }

    #[Route('/step/3', name: 'member_registration_step3')]
    public function step3(Request $request, RegistrationWizardSession $wizard, SettingRepository $settingRepository): Response
    {
        if (!$wizard->hasStep('step2')) {
            return $this->redirectToRoute('member_registration_step2');
        }

        $form = $this->createForm(RegistrationStep3Type::class, $wizard->get('step3') ?? []);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $wizard->set('step3', $form->getData());

            return $this->redirectToRoute('member_registration_step4');
        }

        return $this->render('member/registration_wizard/step3.html.twig', [
            'form' => $form,
            'registrationFee' => $settingRepository->getOrCreate()->getRegistrationFeeAmount(),
        ]);
    }

    #[Route('/step/4', name: 'member_registration_step4')]
    public function step4(
        Request $request,
        RegistrationWizardSession $wizard,
        SectorRepository $sectorRepository,
        SubSectorRepository $subSectorRepository,
        MemberRegistrationService $registrationService,
    ): Response {
        if (!$wizard->hasStep('step3')) {
            return $this->redirectToRoute('member_registration_step3');
        }

        $step1 = $wizard->get('step1');
        $step2 = $wizard->get('step2');
        $step3 = $wizard->get('step3');

        $pinForm = $this->createFormBuilder()
            ->add('pin', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Code PIN (4 chiffres)'],
                'second_options' => ['label' => 'Confirmez le PIN'],
                'invalid_message' => 'Les deux codes PIN ne correspondent pas.',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex(pattern: '/^\d{4}$/', message: 'Le PIN doit contenir exactement 4 chiffres.'),
                ],
            ])
            ->getForm();

        $pinForm->handleRequest($request);

        if ($pinForm->isSubmitted() && $pinForm->isValid()) {
            $member = new Member();
            $member->setFirstName($step1['firstName'])
                ->setLastName($step1['lastName'])
                ->setGender($step1['gender'])
                ->setBirthDate($step1['birthDate'])
                ->setPhone($step1['phone'])
                ->setEmail($step1['email'])
                ->setIdDocumentNumber($step1['idDocumentNumber'])
                ->setProfession($step1['profession'])
                ->setSector($sectorRepository->find($step1['sectorId']))
                ->setCustomSectorLabel($step1['customSectorLabel'])
                ->setWhatsappPhone($step1['whatsappPhone'])
                ->setBeneficiary($step1['beneficiary'])
                ->setActivityLocation($step1['activityLocation'])
                ->setHomeAddress($step1['homeAddress'])
                ->setDailyPaymentAmount((string) $step2['dailyPaymentAmount'])
                ->setEngagementDuration($step2['engagementDuration'])
                ->setSavingsGoal($step2['savingsGoal'])
                ->setGoalDetails($step2['goalDetails'])
                ->setRegistrationFeePaymentMethod($step3['registrationFeePaymentMethod'])
                ->setRegistrationFeeSenderPhone($step3['registrationFeeSenderPhone']);

            if ($step1['subSectorId']) {
                $member->setSubSector($subSectorRepository->find($step1['subSectorId']));
            }

            $registrationService->register($member, $pinForm->get('pin')->getData());

            $wizard->clear();

            return $this->redirectToRoute('member_registration_success', ['memberNumber' => $member->getMemberNumber()]);
        }

        return $this->render('member/registration_wizard/step4.html.twig', [
            'form' => $pinForm,
            'step1' => $step1,
            'step2' => $step2,
            'step3' => $step3,
        ]);
    }

    #[Route('/succes/{memberNumber}', name: 'member_registration_success')]
    public function success(string $memberNumber): Response
    {
        return $this->render('member/registration_success.html.twig', [
            'memberNumber' => $memberNumber,
        ]);
    }
}
