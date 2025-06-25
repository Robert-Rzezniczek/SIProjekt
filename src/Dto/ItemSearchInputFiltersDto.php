<?php

/**
 * Item search input filters DTO.
 */

namespace App\Dto;

/**
 * Class ItemSearchInputFiltersDto.
 */
class ItemSearchInputFiltersDto
{
    /**
     * Constructor.
     *
     * @param string|null $title      Search title
     * @param int|null    $rating     Minimum rating
     * @param int|null    $categoryId Category identifier
     * @param int|null    $tagId      Tag identifier
     */
    public function __construct(public readonly ?string $title = null, public readonly ?int $rating = null, public readonly ?int $categoryId = null, public readonly ?int $tagId = null)
    {
    }
}
