<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiControllerTest extends WebTestCase
{
    public function testAddAction()
    {
        $client = static::createClient();

        $book =
<<<EOT
        {
            "name": "testName",
            "author": "testAuthor",
            "downloadable": true,
            "date": "2016-1-1"
        }
EOT;
        $client->request('POST', '/api/v1/books/add', ['book' => $book, 'apiKey' => 'asdasd']);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $content = json_decode($client->getResponse()->getContent());
        $this->assertEquals(true, $content->success);
    }

    public function testAuthorization()
    {
        $client = static::createClient();

        $client->request('GET', '/api/v1/books', ['apiKey' => 'asdasd']);
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $client->request('GET', '/api/v1/books', ['apiKey' => 'wrongKey']);
        $this->assertEquals(Response::HTTP_UNAUTHORIZED, $client->getResponse()->getStatusCode());
    }
}
