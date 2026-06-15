<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Model\Entity\Tag;
use App\Tests\Functional\FunctionalTestCase;
use Doctrine\ORM\EntityRepository;

// vérifier le filtrage des jeux vidéo par tags
final class FilterTest extends FunctionalTestCase
{
    public function testShouldListTenVideoGames(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->clickLink('2');
        self::assertResponseIsSuccessful();
    }

    public function testShouldFilterVideoGamesBySearch(): void
    {
        $this->get('/');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(10, 'article.game-card');
        $this->client->submitForm('Filtrer', ['filter[search]' => 'Jeu vidéo 49'], 'GET');
        self::assertResponseIsSuccessful();
        self::assertSelectorCount(1, 'article.game-card');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function tagProvider(): array
    {
        // code = code du tag qui est associé à un VideoGame spécifiquement pour le test
        return [
            'cas n°1' => [
                'code' => [1],
                'videogames' => ['Jeu vidéo 0', 'Jeu vidéo 1', 'Jeu vidéo 2'],
            ],
            'cas n°2' => [
                'code' => [1, 2],
                'videogames' => ['Jeu vidéo 0', 'Jeu vidéo 1'],
            ],
            'cas n°3' => [
                'code' => [3],
                'videogames' => ['Jeu vidéo 10', 'Jeu vidéo 11'],
            ],
        ];
    }

    /**
     * @dataProvider tagProvider
     *
     * @param array<int>    $code
     * @param array<string> $expected
     */
    public function testShouldFilterVideoGamesByValidTag(array $code, array $expected): void
    {
        /** @var EntityRepository<Tag> $tagRepository */
        $tagRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Tag::class);

        $tags = [];
        foreach ($code as $item) {
            $tag = $tagRepository->findOneBy(['code' => $item]);
            if (null !== $tag) {
                $tags[] = $tag->getId();
            }
        }

        $router = static::getContainer()->get('router');
        $url = $router->generate('video_games_list', ['filter' => ['tags' => $tags]]);  // génère l'url en fonction de l'id des tags
        $this->get($url);
        self::assertResponseIsSuccessful();

        self::assertSelectorCount(count($expected), 'article.card.game-card');

        foreach ($expected as $title) {
            $this->assertAnySelectorTextContains('h5.game-card-title', $title);
        }
    }

    public function testShouldFilterVideoGamesByInvalidTag(): void
    {
        $router = static::getContainer()->get('router');
        $url = $router->generate('video_games_list', ['filter' => ['tags' => [999]]]);  // tag inexistant
        $this->get($url);
        self::assertResponseIsSuccessful(); // Vérifie que la page répond avec un code 200 (pas d'erreur), même si un tag inexistant est soumis.
    }
}
