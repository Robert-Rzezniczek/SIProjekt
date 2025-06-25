<?php

/**
 * ItemRating fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\ItemRating;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Service\ItemService;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class ItemRatingFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class ItemRatingFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
    /**
     * Constructor.
     *
     * @param ItemService    $itemService    Item Service
     * @param ItemRepository $itemRepository Item Repository
     */
    public function __construct(private readonly ItemService $itemService, private readonly ItemRepository $itemRepository)
    {
    }

    /**
     * Load data.
     *
     * @psalm-suppress PossiblyNullPropertyFetch
     * @psalm-suppress PossiblyNullReference
     * @psalm-suppress UnusedClosureParam
     */
    public function loadData(): void
    {
        if (!$this->manager instanceof ObjectManager || !$this->faker instanceof Generator) {
            return;
        }

        $this->createMany(40, 'item_rating', function (int $i) {
            $itemRating = new ItemRating();
            $itemRating->setItem($this->getRandomReference('item', Item::class));
            $itemRating->setUser($this->getRandomReference('user', User::class));
            $itemRating->setValue($this->faker->numberBetween(1, 5));


            return $itemRating;
        });

        $items = $this->itemRepository->findAll();
        foreach ($items as $item) {
            $this->itemService->updateRatingAverage($item);
        }
    }

    /**
     * This method must return an array of fixtures classes
     * on which the implementing class depends on.
     *
     * @return string[] of dependencies
     *
     * @psalm-return array{0: ItemFixtures::class, 1: UserFixtures::class}
     */
    public function getDependencies(): array
    {
        return [ItemFixtures::class, UserFixtures::class];
    }
}
