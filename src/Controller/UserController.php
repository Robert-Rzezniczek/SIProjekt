<?php

/**
 * User controller.
 */

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\ProfileEditType;
use App\Form\Type\ChangePasswordType;
use App\Form\Type\RoleType;
use App\Service\UserServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UserController.
 */
#[Route('/user')]
class UserController extends AbstractController
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
     * Index action.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'user_index',
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->userService->getPaginatedList($page);

        return $this->render('user/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param User $user User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'user_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function view(User $user): Response
    {
        return $this->render(
            'user/view.html.twig',
            ['user' => $user]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'user_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, User $user): Response
    {
        $form = $this->createForm(
            ProfileEditType::class,
            $user,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('user_edit', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->save($user);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/edit.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Change password action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/change-password',
        name: 'user_change_password',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function changePassword(Request $request, User $user): Response
    {
        $form = $this->createForm(ChangePasswordType::class, null, [
            'method' => 'POST',
            'action' => $this->generateUrl('user_change_password', ['id' => $user->getId()]),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userService->changePasswordFromForm($user, $form);

            $this->addFlash(
                'success',
                $this->translator->trans('message.password_changed_successfully')
            );

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/change_password.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Manage user role action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/roles',
        name: 'user_roles',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function roles(Request $request, User $user): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in and have the correct user type to manage roles.');
        }

        $form = $this->createForm(
            RoleType::class,
            $user,
            [
                'method' => 'POST',
                'action' => $this->generateUrl('user_roles', ['id' => $user->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAdmin = $form->get('isAdmin')->getData();
            if ($this->userService->updateUserRole($user, $currentUser, $isAdmin)) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('message.edited_successfully')
                );
            } else {
                $this->addFlash('error', $this->translator->trans('message.cannot_change_own_role'));
            }

            return $this->redirectToRoute('user_index');
        }

        return $this->render(
            'user/roles.html.twig',
            [
                'form' => $form->createView(),
                'user' => $user,
            ]
        );
    }

    /**
     * Toggle block action.
     *
     * @param Request $request HTTP request
     * @param User    $user    User entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/toggle-block',
        name: 'user_toggle_block',
        requirements: ['id' => '\d+'],
        methods: ['POST']
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function toggleBlock(Request $request, User $user): Response
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if (!$this->userService->toggleBlock($user, $currentUser)) {
            $this->addFlash('error', $this->translator->trans('message.cannot_block_self'));
        } else {
            $this->addFlash(
                'success',
                $this->translator->trans($user->isBlocked() ? 'message.user_blocked' : 'message.user_unblocked')
            );
        }

        return $this->redirectToRoute('user_index');
    }
}
