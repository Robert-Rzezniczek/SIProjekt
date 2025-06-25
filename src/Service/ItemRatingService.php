<?php

/**
 * ItemRating service.
 */

namespace App\Service;

use App\Entity\Item;
use App\Entity\ItemRating;
use App\Entity\User;
use App\Repository\ItemRatingRepository;

/**
 * Class ItemRatingService.
 */
class ItemRatingService implements ItemRatingServiceInterface
{
    /**
     * Construct.
     *
     * @param ItemRatingRepository $itemRatingRepository ItemRatingRepository
     */
    public function __construct(private readonly ItemRatingRepository $itemRatingRepository)
    {
    }

    /**
     * Add or update a rating for an item by a user.
     *
     * @param Item $item        The item being rated
     * @param User $user        The user providing the rating
     * @param int  $ratingValue The rating value (1-5)
     *
     * @return bool Success status
     */
    public function addRating(Item $item, User $user, int $ratingValue): bool
    {

        $existingRating = $this->itemRatingRepository->findOneByItemAndUser($item, $user);
        if ($existingRating) {
            $existingRating->setValue($ratingValue);
            $this->itemRatingRepository->save($existingRating);
        } else {
            $rating = new ItemRating();
            $rating->setItem($item);
            $rating->setUser($user);
            $rating->setValue($ratingValue);
            $this->itemRatingRepository->save($rating);
        }

        return true;
    }
}
