<?php

/**
 * Profile controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ProfileEditType;
use App\Form\Type\ChangePasswordType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ProfileController.
 */
#[Route('/profile')]
class ProfileController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param UserServiceInterface $userService User service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly UserServiceInterface $userService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Profile action.
     *
     * @return Response HTTP response
     */
    #[Route('', name: 'profile_panel', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function panel(): Response
    {
        return $this->render('profile/profile_panel.html.twig');
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/edit', name: 'profile_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileEditType::class, $user, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_edit'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('profile_panel');
        }

        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Change password action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route('/change-password', name: 'profile_change_password', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function changePassword(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ChangePasswordType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('profile_change_password'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->changePasswordFromForm($user, $form);

            $this->addFlash(
                'success',
                $this->translator->trans('message.password_changed_successfully')
            );

            return $this->redirectToRoute('profile_panel');
        }

        return $this->render('profile/change_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
