<?php

declare(strict_types=1);

namespace App\Tests\Functional\VideoGame;

use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AddNoteTest extends FunctionalTestCase
{
    public function testShouldAddNote(): void
    {
        //s'authentifier
        $this->login('user+0@email.com');
        //Faire la requête
        $this->get('/jeu-video-5');
        self::assertResponseIsSuccessful();
        //Récupérer le formulaire
        $form = $this->client->getCrawler()->selectButton('Poster')->form();
        //Intégrer des données dans le formulaire
        $form['review[rating]'] = "2";
        $form['review[comment]'] = "j'ai mis une note de 2";
        //Soumettre le formulaire
        $this->client->submit($form);
        //Vérifier la réponse
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $crawler = $this->client->followRedirect();
        self::assertSelectorTextContains('h1', 'Jeu vidéo 5');
        self::assertAnySelectorTextContains('p', 'j\'ai mis une note de 2');
        self::assertAnySelectorTextContains('h3', 'user+0');
        self::assertAnySelectorTextContains('div.list-group-item div.rating-square span.value', '2');
    }
}