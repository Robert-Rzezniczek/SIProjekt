<?php

/**
 * Item search filters DTO.
 */

namespace App\Dto;

use App\Entity\Category;
use App\Entity\Tag;

/**
 * Class ItemSearchFiltersDto.
 */
class ItemSearchFiltersDto
{
    /**
     * Constructor.
     *
     * @param string|null   $title    Search title
     * @param int|null      $rating   Minimum rating
     * @param Category|null $category Category entity
     * @param Tag|null      $tag      Tag entity
     */
    public function __construct(public readonly ?string $title = null, public readonly ?int $rating = null, public readonly ?Category $category = null, public readonly ?Tag $tag = null)
    {
    }
}
