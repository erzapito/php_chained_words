<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GameControllerTest extends WebTestCase
{

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        self::bootKernel();
        $this->em = static::$kernel->getContainer()
        ->get('doctrine')
        ->getManager()
        ;

        $this->em->createQuery('DELETE AppBundle:Game')->execute();
        $this->em->createQuery('DELETE AppBundle:GameWord')->execute();
    }

    public function testCreateGame_not_logged() {
        $client = static::createClient();
        $crawler = $client->request('POST', '/games/create', array(), array(), array());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testCreateGame_logged() {
        $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $games = $this->em
            ->getRepository('AppBundle:Game')
            ->findAll();

        $this->assertCount(1, $games);
    }

    public function testPlay_not_logged() {
        $client = static::createClient();
        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'),var_export($client->getResponse(),true));
    }

    public function testPlay_logged() {
        $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"winned":false,"last_word":"lea"}', $client->getResponse()->getContent());

        $games = $this->em
            ->getRepository('AppBundle:Game')
            ->findAll();

        $this->assertCount(1, $games);

        $game = $games[0];

        $words = $this->em->getRepository('AppBundle:GameWord')->findBy(array(
            'gameId' => $game->getId(),
        ));

        $this->assertEquals(2, $game->getNumWords());
        $this->assertEquals('lea', $game->getLastWord());

        $this->assertCount(2, $words);

        $this->assertEquals('apple', $words[0]->getWord());
        $this->assertEquals('lea', $words[1]->getWord());
    }

    public function testPlay_no_game() {
        $client = static::createClient();
        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(404, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testPlay_not_valid_word(){
        $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"winned":false,"last_word":"lea"}', $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=straw', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(452, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testPlay_used_word(){
        $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"winned":false,"last_word":"lea"}', $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=lea', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(451, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testPlay_word_not_exists(){
        $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"winned":false,"last_word":"lea"}', $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=earoooooo', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(453, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testPlay_oponent_without_word(){
                $client = static::createClient();

        $crawler = $client->request('POST', '/games/create', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());

        $crawler = $client->request('POST', '/games/play?word=8675309', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"winned":true}', $client->getResponse()->getContent());
    }

}
