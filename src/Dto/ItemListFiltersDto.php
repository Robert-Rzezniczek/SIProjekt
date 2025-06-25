<?php

/**
 * \App\Entity\Item list filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;

/**
 * Class ItemListFiltersDto.
 */
class ItemListFiltersDto
{
    /**
     * Constructor.
     *
     * @param Category|null $category Category entity
     * @param Tag|null      $tag      Tag entity
     */
    public function __construct(public readonly ?Category $category, public readonly ?Tag $tag)
    {
    }
}
