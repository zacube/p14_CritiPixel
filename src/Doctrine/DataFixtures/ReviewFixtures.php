<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class ReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
    ) {}

    public function load(ObjectManager $manager): void
    {
        $users = $manager->getRepository(User::class)->findAll();
        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        foreach ($videoGames as $videoGame) {

            // Nombre aléatoire de reviews entre 1 et 5
            $numberOfReviews = rand(1, 5);

            // mélange les users et en prend n au hasard (sans doublon)
            $selectedUsers = $users;
            shuffle($selectedUsers);
            $selectedUsers = array_slice($selectedUsers, 0, $numberOfReviews);

            $ratings = [];

            foreach ($selectedUsers as $user) {
                $review = new Review();
                $review->setVideoGame($videoGame);
                $review->setUser($user);

                $rating = rand(1, 5);
                $review->setRating($rating);
                $ratings[] = $rating;

                switch ($rating) {
                    case 1:
                        $videoGame->getNumberOfRatingsPerValue()->increaseOne();
                        break;
                    case 2:
                        $videoGame->getNumberOfRatingsPerValue()->increaseTwo();
                        break;
                    case 3:
                        $videoGame->getNumberOfRatingsPerValue()->increaseThree();
                        break;
                    case 4:
                        $videoGame->getNumberOfRatingsPerValue()->increaseFour();
                        break;
                    case 5:
                        $videoGame->getNumberOfRatingsPerValue()->increaseFive();
                        break;
                }

                $review->setComment($this->faker->paragraph());

                $manager->persist($review);
            }

            // Calcul de la moyenne
            $average = array_sum($ratings) / count($ratings);
            $videoGame->setAverageRating(round($average, 2));

            $manager->persist($videoGame);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            VideoGameFixtures::class,
        ];
    }
}