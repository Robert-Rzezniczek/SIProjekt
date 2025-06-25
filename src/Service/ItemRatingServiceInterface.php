<?php

/**
 * ItemRatingService interface.
 */

namespace App\Service;

use App\Entity\Item;
use App\Entity\User;

/**
 * Interface ItemRatingServiceInterface.
 */
interface ItemRatingServiceInterface
{
    /**
     * Add or update a rating for an item by a user.
     *
     * @param Item $item        The item being rated
     * @param User $user        The user providing the rating
     * @param int  $ratingValue The rating value (1-5)
     *
     * @return bool Success status
     */
    public function addRating(Item $item, User $user, int $ratingValue): bool;
}
