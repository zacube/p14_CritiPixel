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
    private KernelBrowser|null $client = null;
    private string $url;
    private User $testUser;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $userRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(User::class);
        $this->testUser = $userRepository->findOneByEmail('user+0@email.com');

        $router = static::getContainer()->get('router');
        $this->url = $router->generate('video_games_show', ['slug' => 'jeu-video-0']);

    }

    public function testResponseUserOk()
    {
        $this->client->loginUser($this->testUser);

        $this->client->request(Request::METHOD_GET, $this->url);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPostReviewValidRating()
    {
        // Vérifie que l'utilisateur existe
        $this->assertNotNull($this->testUser, "Utilisateur test introuvable en base");

        $this->client->loginUser($this->testUser); //$testUser est authentifié

        $crawler = $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful(); // page affichée
        $this->assertSelectorExists('form'); // vérifie la présence du formulaire

        $form = $crawler->selectButton('Poster')->form();
        $form['review[rating]'] = '5';
        $form['review[comment]'] = 'blabla';
        $this->client->submit($form);

        $this->assertResponseRedirects(); //vérifie la redirection 302
        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorNotExists('form'); // le formulaire n'est plus affiché, donc Ok
        $this->assertSelectorTextContains('.rating-5', '5 Note'); //vérifie l'affichage de la note

        // Vérification que l'insertion en base est correcte
        $reviewRepository = $this->client->getContainer()->get('doctrine.orm.entity_manager')->getRepository(Review::class);
        $review = $reviewRepository->findOneBy(['rating' => 5, 'comment' => 'blabla']);
        $this->assertNotNull($review, "La review n'a pas été enregistrée en base");
    }

    public function testPostReviewInvalidRating()
    {
        $this->assertNotNull($this->testUser, "Utilisateur test introuvable en base");
        $this->client->loginUser($this->testUser);

        $crawler = $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Poster')->form();
        $form->disableValidation();
        $form['review[rating]'] = '0';
        $form['review[comment]'] = 'blabla';
        $this->client->submit($form);

        $this->assertResponseStatusCodeSame(422);
    }

    public function testNoFormIfNotAuthenticated()
    {
        $this->client->request(Request::METHOD_GET, $this->url);

        $this->assertResponseIsSuccessful(); // page affichée
        $this->assertSelectorNotExists('form'); // le formulaire n'est pas affiché
    }

    public function testPostReviewNotAuthenticated()
    {
        $this->client->request(Request::METHOD_POST, $this->url, [
            'review' => [
                'rating' => 5,
                'comment' => 'blabla'
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }


}


