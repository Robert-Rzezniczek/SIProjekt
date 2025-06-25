<?php

/**
 * Reservation controller.
 */

namespace App\Controller;

use App\Entity\Item;
use App\Entity\Reservation;
use App\Form\Type\RentType;
use App\Form\Type\ReservationType;
use App\Form\Type\ReturnType;
use App\Repository\ItemRepository;
use App\Security\Voter\ReservationVoter;
use App\Service\ReservationServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ReservationController.
 */
#[Route('/reservation')]
class ReservationController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param ReservationServiceInterface $reservationService Reservation service
     * @param TranslatorInterface         $translator         Translator
     * @param ItemRepository              $itemRepository     Item repository
     */
    public function __construct(private readonly ReservationServiceInterface $reservationService, private readonly TranslatorInterface $translator, private readonly ItemRepository $itemRepository)
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
        name: 'reservation_index',
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function index(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->reservationService->getPaginatedList($page);

        return $this->render('reservation/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Reservation $reservation Reservation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'reservation_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function view(Reservation $reservation): Response
    {
        return $this->render(
            'reservation/view.html.twig',
            ['reservation' => $reservation]
        );
    }

    /**
     * Reserve action (create reservation request).
     *
     * @param Request $request HTTP request
     * @param int     $id      Item ID
     *
     * @return Response|RedirectResponse HTTP response
     */
    #[Route(
        '/reserve/{id}',
        name: 'reservation_reserve',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST',
    )]
    public function reserve(Request $request, int $id): Response|RedirectResponse
    {
        $item = $this->itemRepository->find($id);
        if (!$item) {
            throw $this->createNotFoundException('Item not found');
        }

        $reservation = new Reservation();
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->reservationService->createReservation($reservation, $item)) {
                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('item_index');
            }
            $this->addFlash('error', $this->translator->trans('message.reservation_failed'));
        }

        return $this->render(
            'reservation/reservation.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Rent action (approve and activate reservation).
     *
     * @param Request $request HTTP request
     * @param int     $id      Item ID
     *
     * @return Response|RedirectResponse HTTP response
     */
    #[Route(
        '/rent/{id}',
        name: 'reservation_rent',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|POST'
    )]
    #[IsGranted('ROLE_USER')]
    public function rent(Request $request, int $id): Response|RedirectResponse
    {
        $user = $this->getUser();
        $reservation = $this->reservationService->initializeReservation($id, $user);

        if (!$reservation) {
            $this->addFlash('error', $this->translator->trans('message.item_not_available'));

            return $this->redirectToRoute('item_index');
        }

        if (!$this->isGranted(ReservationVoter::RENT, $reservation)) {
            $this->addFlash('error', $this->translator->trans('message.access_denied'));

            return $this->redirectToRoute('item_index');
        }

        $form = $this->createForm(RentType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->reservationService->rentItem($form, $id, $user)) {
                $this->addFlash('success', $this->translator->trans('message.rented_successfully'));

                return $this->redirectToRoute('reservation_my');
            }
            $this->addFlash('error', $this->translator->trans('message.rent_failed'));

            return $this->redirectToRoute('item_index');
        }

        return $this->render(
            'reservation/rent.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Manage reservation action.
     *
     * @param Request     $request     HTTP request
     * @param Reservation $reservation Reservation entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/manage/{id}',
        name: 'reservation_manage',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted(ReservationVoter::MANAGE, subject: 'reservation')]
    public function manage(Request $request, Reservation $reservation): Response
    {
        $decision = $request->query->get('decision');

        if ($decision && $this->reservationService->processReservationDecision($reservation, $decision)) {
            $this->addFlash('success', $this->translator->trans('message.managed_successfully'));

            return $this->redirectToRoute('reservation_index');
        }
        if ($decision) {
            $this->addFlash('warning', $this->translator->trans('message.action_not_allowed'));

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/manage.html.twig', ['reservation' => $reservation]);
    }

    /**
     * List reservations for user.
     *
     * @param int $page Page int
     *
     * @return Response HTTP response
     */
    #[Route(
        '/my-reservations',
        name: 'reservation_my',
        methods: 'GET'
    )]
    #[IsGranted('ROLE_USER')]
    public function myReservations(#[MapQueryParameter] int $page = 1): Response
    {
        $user = $this->getUser();
        $pagination = $this->reservationService->getPaginatedListForUser($page, $user);

        $hasOverdue = $this->reservationService->hasOverdueReservations($user);
        if ($hasOverdue) {
            $this->addFlash('warning', $this->translator->trans('message.overdue_reservations_warning'));
        }

        return $this->render('reservation/my_reservations.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * Return action (initiate return request by user).
     *
     * @param Request     $request     HTTP request
     * @param Reservation $reservation Reservation entity
     *
     * @return Response|RedirectResponse HTTP response|Redirect Response
     */
    #[Route(
        '/return/{id}',
        name: 'reservation_return',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST']
    )]
    #[IsGranted(ReservationVoter::RETURN, subject: 'reservation', message: 'message.access_denied')]
    public function return(Request $request, Reservation $reservation): Response|RedirectResponse
    {
        $user = $this->getUser();

        $form = $this->createForm(ReturnType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->reservationService->returnItem($form, $reservation->getId(), $user)) {
                $this->addFlash('success', $this->translator->trans('message.return_requested_successfully'));

                return $this->redirectToRoute('reservation_my');
            }
            $this->addFlash('error', $this->translator->trans('message.return_failed'));
        }

        return $this->render(
            'reservation/return.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Manage return action.
     *
     * @param Request     $request     HTTP request
     * @param Reservation $reservation Reservation entity
     *
     * @return Response|RedirectResponse HTTP response
     */
    #[Route(
        '/manage-return/{id}',
        name: 'reservation_manage_return',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted(ReservationVoter::MANAGE_RETURN, subject: 'reservation')]
    public function manageReturn(Request $request, Reservation $reservation): Response|RedirectResponse
    {
        $decision = $request->query->get('decision');

        if ($decision && $this->reservationService->processReturnDecision($reservation, $decision)) {
            $this->addFlash(
                'success',
                $this->translator->trans('label.return_managed_successfully')
            );

            return $this->redirectToRoute('reservation_index');
        }
        if ($decision) {
            $this->addFlash('warning', $this->translator->trans('label.action_not_allowed'));

            return $this->redirectToRoute('reservation_index');
        }

        return $this->render('reservation/manage_return.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    /**
     * List overdue reservations.
     *
     * @param int $page Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        '/overdue',
        name: 'reservation_overdue',
        methods: 'GET'
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function overdueReservations(#[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->reservationService->getPaginatedOverdueReservations($page);

        return $this->render('reservation/overdue.html.twig', [
            'pagination' => $pagination,
        ]);
    }
}
