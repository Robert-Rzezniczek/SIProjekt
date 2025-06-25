<?php

/**
 * Reservation fixtures.
 */

namespace App\DataFixtures;

use App\Entity\Item;
use App\Entity\Reservation;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

/**
 * Class ReservationFixtures.
 *
 * @psalm-suppress MissingConstructor
 */
class ReservationFixtures extends AbstractBaseFixtures implements DependentFixtureInterface
{
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
        $this->createMany(10, 'reservation', function (int $i) {
            $reservation = new Reservation();
            $reservation->setItem($this->getRandomReference('item', Item::class));
            $user = $this->getRandomReference('user', User::class);
            $reservation->setUser($user);
            $reservation->setEmail($user->getEmail());
            $reservation->setComment($this->faker->optional()->sentence(6));
            $reservation->setStatus('approved');
            $reservation->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-30 days', '-1 day')
                )
            );
            $reservation->setLoanDate(new \DateTimeImmutable());
            $reservation->setExpirationDate(
                (new \DateTimeImmutable())->modify('+'.$this->faker->numberBetween(3, 14).' days')
            );

            return $reservation;
        });
        $statuses = ['pending', 'rejected', 'approved'];
        $this->createMany(10, 'reservation_guest', function (int $i) use ($statuses) {
            $reservation = new Reservation();
            $reservation->setItem($this->getRandomReference('item', Item::class));
            $reservation->setUser(null);
            $reservation->setEmail($this->faker->safeEmail());
            $reservation->setNickname($this->faker->userName());
            $reservation->setComment($this->faker->optional()->sentence(6));
            $reservation->setStatus($this->faker->randomElement($statuses));
            $reservation->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-30 days', '-1 day')
                )
            );
            $reservation->setLoanDate(null);
            $reservation->setExpirationDate(null);

            return $reservation;
        });

        $this->createMany(15, 'reservation_overdue', function (int $i) {
            $reservation = new Reservation();
            $reservation->setItem($this->getRandomReference('item', Item::class));
            $user = $this->getRandomReference('user', User::class);
            $reservation->setUser($user);
            $reservation->setEmail($user->getEmail());
            $reservation->setComment($this->faker->optional()->sentence(6));
            $reservation->setStatus('approved');
            $reservation->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-40 days', '-31 days')
                )
            );
            $reservation->setLoanDate(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-35 days', '-26 days')
                )
            );
            $reservation->setExpirationDate(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-30 days', '-15 days')
                )
            );
            $reservation->setReturnDate(null);

            return $reservation;
        });
        $this->createMany(15, 'reservation_late_return', function (int $i) {
            $reservation = new Reservation();
            $reservation->setItem($this->getRandomReference('item', Item::class));
            $user = $this->getRandomReference('user', User::class);
            $reservation->setUser($user);
            $reservation->setEmail($user->getEmail());
            $reservation->setComment($this->faker->optional()->sentence(6));
            $reservation->setStatus('returned');
            $reservation->setCreatedAt(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-40 days', '-31 days')
                )
            );
            $reservation->setLoanDate(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-35 days', '-26 days')
                )
            );
            $reservation->setExpirationDate(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-30 days', '-15 days')
                )
            );
            $reservation->setReturnDate(
                \DateTimeImmutable::createFromMutable(
                    $this->faker->dateTimeBetween('-14 days', '-1 day')
                )
            );

            return $reservation;
        });
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
