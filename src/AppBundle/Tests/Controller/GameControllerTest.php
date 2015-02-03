<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\UserBundle\Entity\User;
use AppBundle\Entity\Game;

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
        $this->em->createQuery('DELETE CWUserBundle:User')->execute();
    }

    public function testCreateGame_not_logged() {
        $client = static::createClient();
        $crawler = $client->request('POST', '/games/create', array(), array(), array());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testCreateGame_logged() {
    	$this->createTestUser();
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
    	$this->createTestUser();
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
    	$this->createTestUser();
        $client = static::createClient();
        $crawler = $client->request('POST', '/games/play?word=apple', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(404, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testPlay_not_valid_word(){
    	$this->createTestUser();
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
    	$this->createTestUser();
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
    	$this->createTestUser();
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
    	$this->createTestUser();
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

    public function testCurrent_not_logged(){
        $client = static::createClient();
        $crawler = $client->request('GET', '/games/current', array(), array(), array());
        $this->assertTrue($client->getResponse()->isRedirect('http://localhost/login'));
    }

    public function testCurrent_no_game(){
    	$this->createTestUser();
        $client = static::createClient();
        $crawler = $client->request('GET', '/games/current', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));
        $this->assertEquals(404, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
    }

    public function testCurrent_info() {
    	$this->createTestUser();
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

        $crawler = $client->request('GET', '/games/current', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('{"total_words":2,"last_words":["lea","apple"]}', $client->getResponse()->getContent());
    }

    public function testBestUsers_no_users() {
    	$this->createTestUser();
    	$client = static::createClient();
    	
    	$crawler = $client->request('GET', '/games/best', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('[]', $client->getResponse()->getContent());
    }
    
    public function testBestUsers_more_than_five() {
    	$this->createTestUser();
    	
    	// dummy games
    	$game = new Game();
    	$game->setUser('A0');
    	$game->setLastWord('A0');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(12);
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A1');
    	$game->setLastWord('W1');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(10);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A2');
    	$game->setLastWord('W2');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(9);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A3');
    	$game->setLastWord('W3');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(8);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A4');
    	$game->setLastWord('W4');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(7);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A5');
    	$game->setLastWord('W5');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(6);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	
    	$game = new Game();
    	$game->setUser('A6');
    	$game->setLastWord('W6');
    	$game->setCreationDate(new \DateTime());
    	$game->setNumWords(5);
    	$game->setEndDate(new \DateTime());
    	$this->em->persist($game);
    	$this->em->flush();
    	
    	$client = static::createClient();
    	
    	$crawler = $client->request('GET', '/games/best', array(), array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userpass',
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertEquals('[{"user":"A1","last_word":"W1","total_words":10},{"user":"A2","last_word":"W2","total_words":9},{"user":"A3","last_word":"W3","total_words":8},{"user":"A4","last_word":"W4","total_words":7},{"user":"A5","last_word":"W5","total_words":6}]', $client->getResponse()->getContent());
    }
    
    private function createTestUser() {
    	$encoder = static::$kernel->getContainer()->get('security.password_encoder');
    	$user = new User();
    	$user->setUsername('user');
    	$user->setEmail('ee');
    	$user->setPassword($encoder->encodePassword($user, 'userpass'));
    	$this->em->persist($user);
    	$this->em->flush();
    }

}
