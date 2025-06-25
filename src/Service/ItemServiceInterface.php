<?php

/**
 * Item service interface.
 */

namespace App\Service;

use App\Dto\ItemListInputFiltersDto;
use App\Dto\ItemSearchInputFiltersDto;
use App\Entity\Item;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Symfony\Component\Form\FormInterface;

/**
 * Interface ItemServiceInterface.
 */
interface ItemServiceInterface
{
    /**
     * Get paginated list.
     *
     * @param int                     $page    Page number
     * @param ItemListInputFiltersDto $filters Filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated list
     */
    public function getPaginatedList(int $page, ItemListInputFiltersDto $filters): PaginationInterface;

    /**
     * Save entity.
     *
     * @param Item $item Item entity
     */
    public function save(Item $item): void;

    /**
     * Delete entity.
     *
     * @param Item $item Item entity
     */
    public function delete(Item $item): void;

    /**
     * Update item with image upload.
     *
     * @param FormInterface $form Form instance of ItemType
     * @param Item          $item Item entity
     */
    public function update(FormInterface $form, Item $item): void;

    /**
     * Create item with image upload.
     *
     * @param FormInterface $form Form instance of ItemType
     * @param Item          $item Item entity
     */
    public function create(FormInterface $form, Item $item): void;

    /**
     * Remove image from item.
     *
     * @param Item $item Item entity
     */
    public function removeImage(Item $item): void;

    /**
     * Search items based on filters.
     *
     * @param string|null $title Search title
     *
     * @return Item[] List of matching items
     */
    public function searchItems(?string $title = null): array;

    /**
     * Get paginated search results.
     *
     * @param int                       $page    Page number
     * @param ItemSearchInputFiltersDto $filters Search filters
     *
     * @return PaginationInterface<SlidingPagination> Paginated search results
     */
    public function getPaginatedSearchResults(int $page, ItemSearchInputFiltersDto $filters): PaginationInterface;
}
