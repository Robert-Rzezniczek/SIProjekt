<?php

/**
 * Item service.
 */

namespace App\Service;

use App\Dto\ItemListFiltersDto;
use App\Dto\ItemListInputFiltersDto;
use App\Dto\ItemSearchFiltersDto;
use App\Dto\ItemSearchInputFiltersDto;
use App\Entity\Item;
use App\Repository\ItemRatingRepository;
use App\Repository\ItemRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Class ItemService.
 */
class ItemService implements ItemServiceInterface
{
    /**
     * Items per page.
     *
     * Use constants to define configuration options that rarely change instead
     * of specifying them in app/config/config.yml.
     * See https://symfony.com/doc/current/best_practices.html#configuration
     *
     * @constant int
     */
    private const PAGINATOR_ITEMS_PER_PAGE = 10;

    /**
     * Constructor.
     *
     * @param CategoryServiceInterface   $categoryService       CategoryServiceInterface
     * @param PaginatorInterface         $paginator             PaginatorInterface
     * @param TagServiceInterface        $tagService            TagServiceInterface
     * @param ItemRepository             $itemRepository        ItemRepository
     * @param ReservationRepository      $reservationRepository ReservationRepository
     * @param ItemRatingRepository       $itemRatingRepository  ItemRatingRepository
     * @param FileUploadServiceInterface $fileUploadService     FileUploadServiceInterface
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly PaginatorInterface $paginator, private readonly TagServiceInterface $tagService, private readonly ItemRepository $itemRepository, private readonly ReservationRepository $reservationRepository, private readonly ItemRatingRepository $itemRatingRepository, private readonly FileUploadServiceInterface $fileUploadService)
    {
    }

    /**
     * Get paginated list.
     *
     * @param int                     $page    Page number
     * @param ItemListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedList(int $page, ItemListInputFiltersDto $filters): PaginationInterface
    {
        $filters = $this->prepareFilters($filters);

        return $this->paginator->paginate(
            $this->itemRepository->queryAll($filters),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['item.id', 'item.createdAt', 'item.updatedAt', 'item.title', 'item.rating', 'item.quantity'],
                'defaultSortFieldName' => 'item.updatedAt',
                'defaultSortDirection' => 'desc',
            ]
        );
    }

    /**
     * Save entity.
     *
     * @param Item $item Item entity
     */
    public function save(Item $item): void
    {
        $this->itemRepository->save($item);
    }

    /**
     * Delete entity with image and ratings removal.
     *
     * @param Item $item Item entity
     */
    public function delete(Item $item): void
    {
        $ratings = $this->itemRatingRepository->findBy(['item' => $item]);
        foreach ($ratings as $rating) {
            $this->itemRatingRepository->delete($rating);
        }

        if ($item->getImageFilename()) {
            $this->removeImage($item);
        }

        $this->itemRepository->delete($item);
    }

    /**
     * Can Item be deleted?
     *
     * @param Item $item Item entity
     *
     * @return bool Result
     */
    public function canBeDeleted(Item $item): bool
    {
        try {
            $result = $this->reservationRepository->countReservationsByItem($item);

            return !($result > 0);
        } catch (NoResultException|NonUniqueResultException) {
            return false;
        }
    }

    /**
     * Update the average rating for an item.
     *
     * @param Item $item The item to update
     */
    public function updateRatingAverage(Item $item): void
    {
        $ratings = $this->itemRatingRepository->findByItem($item);

        if (empty($ratings)) {
            $item->setRating(null);
        } else {
            $total = array_sum(array_map(fn ($rating) => $rating->getValue(), $ratings));
            $average = $total / count($ratings);
            $item->setRating(round($average, 2));
        }

        $this->itemRepository->save($item);
    }

    /**
     * Get top-rated items.
     *
     * @param int $limit The number of items to return
     *
     * @return array List of items sorted by rating
     */
    public function getTopRatedItems(int $limit = 10): array
    {
        return $this->itemRepository->findBy([], ['rating' => 'DESC'], $limit, null, ['rating' => 'IS NOT NULL']);
    }

    /**
     * Create item with image upload.
     *
     * @param FormInterface $form Form instance of ItemType
     * @param Item          $item Item entity
     */
    public function create(FormInterface $form, Item $item): void
    {
        $this->handleImageUpload($form, $item);
    }

    /**
     * Update item with image upload.
     *
     * @param FormInterface $form Form instance of ItemType
     * @param Item          $item Item entity
     */
    public function update(FormInterface $form, Item $item): void
    {
        $this->handleImageUpload($form, $item);
    }

    /**
     * Remove image from item.
     *
     * @param Item $item Item entity
     */
    public function removeImage(Item $item): void
    {
        $this->handleImageUpload(null, $item, true);
    }

    /**
     * Search items based on title.
     *
     * @param string|null $title Search title
     *
     * @return Item[] List of matching items
     */
    public function searchItems(?string $title = null): array
    {
        return $this->itemRepository->findBySearchCriteria($title);
    }

    /**
     * Get paginated search results.
     *
     * @param int                       $page    Page number
     * @param ItemSearchInputFiltersDto $filters Search filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated search results
     */
    public function getPaginatedSearchResults(int $page, ItemSearchInputFiltersDto $filters): PaginationInterface
    {
        $preparedFilters = new ItemSearchFiltersDto(
            $filters->title,
            $filters->rating,
            $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );

        return $this->paginator->paginate(
            $this->itemRepository->querySearchResults(
                $preparedFilters->title,
                $preparedFilters->rating,
                $preparedFilters->category,
                $preparedFilters->tag
            ),
            $page,
            self::PAGINATOR_ITEMS_PER_PAGE,
            [
                'sortFieldAllowList' => ['item.id', 'item.createdAt', 'item.updatedAt', 'item.title', 'item.rating', 'category.title'],
                'defaultSortFieldName' => 'item.title',
                'defaultSortDirection' => 'asc',
            ]
        );
    }

    /**
     * Prepare filters for the items list.
     *
     * @param ItemListInputFiltersDto $filters Raw filters from request
     *
     * @return ItemListFiltersDto Result filters
     */
    private function prepareFilters(ItemListInputFiltersDto $filters): ItemListFiltersDto
    {
        return new ItemListFiltersDto(
            null !== $filters->categoryId ? $this->categoryService->findOneById($filters->categoryId) : null,
            null !== $filters->tagId ? $this->tagService->findOneById($filters->tagId) : null
        );
    }

    /**
     * Handle image upload or removal for an item.
     *
     * @param FormInterface|null $form        Form instance of ItemType (optional)
     * @param Item               $item        Item entity
     * @param bool               $removeImage Whether to remove the existing image (used when $form is null)
     */
    private function handleImageUpload(?FormInterface $form, Item $item, bool $removeImage = false): void
    {
        $imageFile = $form ? $form->get('image')->getData() : null;
        $removeImageFromForm = $form ? $form->get('removeImage')->getData() : $removeImage;

        if ($removeImageFromForm && $item->getImageFilename()) {
            $filePath = $this->fileUploadService->getTargetDirectory().'/'.$item->getImageFilename();
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $item->setImageFilename(null);
        } elseif ($imageFile) {
            if ($item->getImageFilename()) {
                $oldFilePath = $this->fileUploadService->getTargetDirectory().'/'.$item->getImageFilename();
                if (file_exists($oldFilePath)) {
                    unlink($oldFilePath);
                }
            }
            $imageFilename = $this->fileUploadService->upload($imageFile);
            $item->setImageFilename($imageFilename);
        }
        $this->save($item);
    }
}
