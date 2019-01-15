<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    private $client = null;

    public function setUp()
    {
        self::bootKernel();

        $this->client = static::createClient();
        $this->em = static::$kernel->getContainer()->get('doctrine')->getManager();
    }

    protected function tearDown()
    {
        parent::tearDown();

        $this->em->close();
    }

    public function testAddAndRemoveBook()
    {
        $this->client->request('GET', '/book');
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $crawler = $this->client->followRedirect();

        $form = $crawler->selectButton("Log in")->form(array(
            "_username"  => "admin",
            "_password"  => "asdasd",
        ));

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $crawler = $this->client->request("GET", "/book");
        $name = 'name' . time();
        $form = $crawler->selectButton("Add Book")->form(array(
            "book[name]"  => $name,
            "book[author]"  => 'author',
            "book[date][year]"  => '2016',
            "book[date][month]"  => '1',
            "book[date][day]"  => '1',
            "book[downloadable]"  => true,
            "book[image]" => new UploadedFile(
                realpath(dirname(__FILE__) . '/../../..') . '/web/upload/photo.jpg',
                'photo.jpg',
                'image/jpeg',
                123
            )
        ));

        $this->client->submit($form);
        $this->client->followRedirect();

        $book = current($this->em->getRepository('AppBundle:Book')->findBy(['name' => $name], []));
        $imagePath = realpath(dirname(__FILE__) . '/../../..') . '/web/upload/' . $book->getImage();
        $this->assertTrue(file_exists($imagePath));
        $this->em->remove($book);
        $this->em->flush();
        $this->assertFalse(file_exists($imagePath));
    }
}
