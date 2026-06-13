<?php

namespace App\Tests\Controller;

use App\Model\Entity\Review;
use App\Model\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class VideoGameControllerTest extends WebTestCase
{
    private ?KernelBrowser $client = null;
    private string $url;
    private User $testUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->testUser = $userRepository->findOneBy(['email' => 'user+0@email.com']);

        $router = static::getContainer()->get('router');
        $this->url = $router->generate('video_games_show', ['slug' => 'jeu-video-0']);
    }

    public function testResponseUserOk(): void
    {
        $this->client->loginUser($this->testUser);

        $this->client->request(Request::METHOD_GET, $this->url);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPostReviewValidRating(): void
    {
        $this->client->loginUser($this->testUser); // $testUser est authentifié

        $crawler = $this->client->request(Request::METHOD_GET, $this->url); // on demande la page

        $this->assertResponseIsSuccessful(); // la page est affichée.
        $this->assertSelectorExists('form'); // on vérifie la présence du formulaire

        $form = $crawler->selectButton('Poster')->form(); // cherche la présence du bouton "Poster"
        $form['review[rating]'] = '5';
        $form['review[comment]'] = 'blabla';
        $this->client->submit($form);

        $this->assertResponseRedirects(); // vérifie la redirection 302
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('form'); // le formulaire n'est plus affiché, donc Ok
        $this->assertSelectorTextContains('#pane-reviews .rating-5', '5 Note'); // vérifie l'affichage de la note

        // Vérification que l'insertion en base est correcte
        $reviewRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Review::class);
        $review = $reviewRepository->findOneBy(['rating' => 5, 'comment' => 'blabla']);
        $this->assertNotNull($review, "La review n'a pas été enregistrée en base");
    }

    public function testPostReviewInvalidRating(): void
    {
        $this->client->loginUser($this->testUser);

        $crawler = $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation(); // désactive la validation du formulaire côté client
        $form['review[rating]'] = '0';
        $form['review[comment]'] = 'blabla';
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422); // → renvoie le code 422 Unprocessable Content
    }

    public function testPostReviewInvalidComment(): void
    {
        $this->client->loginUser($this->testUser);

        $crawler = $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation(); // désactive la validation du formulaire côté client
        $form['review[rating]'] = '5';
        $form['review[comment]'] = 'Un commentaire de plus de 50 caractères est refusé.';
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422); // → renvoie le code 422 Unprocessable Content
    }

    public function testPostReviewInvalidComment(): void
    {
        $this->client->loginUser($this->testUser);

        $crawler = $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation(); // désactive la validation du formulaire côté client
        $form['review[rating]'] = '5';
        $form['review[comment]'] = 'Un commentaire de plus de 50 caractères est refusé.';
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422); // -> renvoie le code 422 Unprocessable Content
    }

    public function testNoFormIfNotAuthenticated(): void
    {
        $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful(); // page affichée
        $this->assertSelectorNotExists('form'); // le formulaire n'est pas affiché
    }

    public function testPostReviewNotAuthenticated(): void
    {
        $this->client->request(Request::METHOD_POST, $this->url, [
            'review' => [
                'rating' => 5,
                'comment' => 'blabla',
            ],
        ]);

        $this->assertResponseStatusCodeSame(401); // -> renvoie le code 401 Unauthorized
    }
}
