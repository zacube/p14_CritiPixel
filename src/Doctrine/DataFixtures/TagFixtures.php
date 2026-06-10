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
        // Crée 10 tags
        $tags = array_fill_callback(0, 10, fn (int $index): Tag => (new Tag)
            ->setCode(($index) + 1)
            ->setName($this->faker->word())
        );
        array_walk($tags, [$manager, 'persist']);

        $videoGames = $manager->getRepository(VideoGame::class)->findAll();

        // Associations déterministes pour les tests
        $videoGames[0]->addTag($tags[0])->addTag($tags[1]);
        $videoGames[1]->addTag($tags[0])->addTag($tags[1]);
        $videoGames[2]->addTag($tags[0]);
        $videoGames[10]->addTag($tags[2]);
        $videoGames[11]->addTag($tags[2]);

        // Reste des jeux : associations aléatoires
        foreach ($videoGames as $index => $videoGame) {
            if (in_array($index, [0, 1, 2, 10, 11])) {
                continue;
            }
            $numberOfTags = rand(1, 3);
            $selectedTags = array_slice($tags, 3); //retire les 3 premiers tags utilisés pour le test
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