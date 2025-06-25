<?php

/**
 * Item controller.
 */

namespace App\Controller;

use App\Dto\ItemListInputFiltersDto;
use App\Dto\ItemSearchInputFiltersDto;
use App\Entity\Item;
use App\Form\Type\ItemSearchType;
use App\Form\Type\ItemType;
use App\Resolver\ItemListInputFiltersDtoResolver;
use App\Security\Voter\ItemVoter;
use App\Service\ItemServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ItemController.
 */
#[Route('/item')]
class ItemController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param ItemServiceInterface $itemService Item service
     * @param TranslatorInterface  $translator  Translator
     */
    public function __construct(private readonly ItemServiceInterface $itemService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param ItemListInputFiltersDto $filters Input filters
     * @param int                     $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'item_index',
        methods: 'GET'
    )]
    public function index(#[MapQueryString(resolver: ItemListInputFiltersDtoResolver::class)] ItemListInputFiltersDto $filters, #[MapQueryParameter] int $page = 1): Response
    {
        $pagination = $this->itemService->getPaginatedList(
            $page,
            $filters
        );

        return $this->render('item/index.html.twig', ['pagination' => $pagination]);
    }

    /**
     * View action.
     *
     * @param Item $item Item entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'item_view',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET'
    )]
    #[IsGranted(ItemVoter::VIEW, subject: 'item')]
    public function view(Item $item): Response
    {
        return $this->render(
            'item/view.html.twig',
            ['item' => $item]
        );
    }

    /**
     * Create action.
     *
     * @param Request $request HTTP request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/create',
        name: 'item_create',
        methods: 'GET|POST',
    )]
    #[IsGranted(ItemVoter::CREATE)]
    public function create(Request $request): Response
    {
        $item = new Item();
        $form = $this->createForm(
            ItemType::class,
            $item,
            ['action' => $this->generateUrl('item_create')]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->itemService->create($form, $item);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('item_index');
        }

        return $this->render(
            'item/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request $request HTTP request
     * @param Item    $item    Item entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'item_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|PUT'
    )]
    #[IsGranted(ItemVoter::EDIT, subject: 'item')]
    public function edit(Request $request, Item $item): Response
    {
        $form = $this->createForm(
            ItemType::class,
            $item,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('item_edit', ['id' => $item->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->itemService->update($form, $item);

            $this->addFlash(
                'success',
                $this->translator->trans('message.edited_successfully')
            );

            return $this->redirectToRoute('item_index');
        }

        return $this->render(
            'item/edit.html.twig',
            [
                'form' => $form->createView(),
                'item' => $item,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request $request HTTP request
     * @param Item    $item    Item entity
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'item_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: 'GET|DELETE'
    )]
    #[IsGranted(ItemVoter::DELETE, subject: 'item')]
    public function delete(Request $request, Item $item): Response
    {
        if (!$this->itemService->canBeDeleted($item)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.item_contains_reservations')
            );

            return $this->redirectToRoute('item_index');
        }
        $form = $this->createForm(FormType::class, $item, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('item_delete', ['id' => $item->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->itemService->delete($item);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('item_index');
        }

        return $this->render(
            'item/delete.html.twig',
            [
                'form' => $form->createView(),
                'item' => $item,
            ]
        );
    }

    /**
     * View top-rated items.
     *
     * @return Response HTTP response
     */
    #[Route('/top-rated', name: 'item_top_rated', methods: ['GET'])]
    public function topRated(): Response
    {
        $topRatedItems = $this->itemService->getTopRatedItems();

        return $this->render('item/top_rated.html.twig', [
            'topRatedItems' => $topRatedItems,
        ]);
    }

    /**
     * Search action.
     *
     * @param Request                   $request HTTP request
     * @param ItemSearchInputFiltersDto $filters Search filters
     * @param int                       $page    Page number
     *
     * @return Response HTTP response
     */
    #[Route('/search', name: 'item_search', methods: 'GET')]
    public function search(Request $request, ItemSearchInputFiltersDto $filters, int $page = 1): Response
    {
        $form = $this->createForm(ItemSearchType::class);
        $form->handleRequest($request);

        $pagination = $this->itemService->getPaginatedSearchResults($page, $filters);

        return $this->render('item/search.html.twig', [
            'form' => $form->createView(),
            'pagination' => $pagination,
        ]);
    }
}
