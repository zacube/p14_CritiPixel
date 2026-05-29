<?php

namespace App\Doctrine\DataFixtures;

use App\Model\Entity\Review;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Model\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Generator;

class TagFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly Generator $faker,
    )
    {
    }

    public function load(ObjectManager $manager): void
    {
        //crée 10 tags
        $tags = array_fill_callback(0, 10, fn (int $index): Tag => (new Tag)
            ->setCode(($index) + 1)
            ->setName($this->faker->word())
        );
        array_walk($tags, [$manager, 'persist']);

        $videoGames = $manager->getRepository(VideoGame::class)->findAll();
        foreach ($videoGames as $videoGame){

            // Nombre aléatoire de tags entre 1 et 3.
            $numberOfTags = rand(1, 3);

            // mélange les tags et en prend n au hasard
            $selectedTags = $tags;
            shuffle($selectedTags);
            $selectedTags = array_slice($selectedTags, 0, $numberOfTags);

            foreach ($selectedTags as $tag) {
                $videoGame->addTag($tag);
            }
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