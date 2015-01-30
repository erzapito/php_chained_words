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

}
