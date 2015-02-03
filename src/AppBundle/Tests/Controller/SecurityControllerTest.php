<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Acme\UserBundle\Entity\User;

class SecurityControllerTest extends WebTestCase {
	
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
	
		$this->em->createQuery('DELETE CWUserBundle:User')->execute();
	}
	
	public function testPasswordsDontMatch() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas';
		$form['password'] = 'pass1';
		$form['repeat_password'] = 'pass2';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Passwords don&#039;t match/', $client->getResponse()->getContent());
	}
	
	public function testnousername() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = '';
		$form['password'] = 'pass1';
		$form['repeat_password'] = 'pass1';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Username is mandatory/', $client->getResponse()->getContent());
	}
	
	public function testnoemail() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
	
		$form = $crawler->selectButton('Register')->form();
	
		$form['username'] = '';
		$form['password'] = 'pass1';
		$form['repeat_password'] = 'pass1';
		$crawler = $client->submit($form);
	
		$this->assertRegExp('/Email is mandatory/', $client->getResponse()->getContent());
	}
	
	public function testPasswordsShort() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas';
		$form['password'] = 'ps';
		$form['repeat_password'] = 'ps';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Password must be at least 4 characters long/', $client->getResponse()->getContent());
	}
	
	public function testPasswordsSimple() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas';
		$form['password'] = 'ps';
		$form['repeat_password'] = 'ps';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Password should contain a number or a unicorn horn/', $client->getResponse()->getContent());
	}
	
	public function testUserExists() {
		$user = new User();
		$user->setUsername('Lucas');
		$user->setPassword('ws');
		$user->setEmail('ee');
		$this->em->persist($user);
		$this->em->flush();
		
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas';
		$form['password'] = 'ps';
		$form['repeat_password'] = 'ps';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Username already in use/', $client->getResponse()->getContent());
	}
	
	public function testEmailExists() {
		$user = new User();
		$user->setUsername('Lucas');
		$user->setPassword('ws');
		$user->setEmail('test@email.com');
		$this->em->persist($user);
		$this->em->flush();
		
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas2';
		$form['password'] = 'ps';
		$form['repeat_password'] = 'ps';
		$form['email'] = 'test@email.com';
		$crawler = $client->submit($form);
		
		$this->assertRegExp('/Email already in use/', $client->getResponse()->getContent());
	}
	
	public function testValid() {
		$client = static::createClient();
		$crawler = $client->request('GET', '/registration');
		
		$form = $crawler->selectButton('Register')->form();
		
		$form['username'] = 'Lucas';
		$form['password'] = 'pass1';
		$form['repeat_password'] = 'pass1';
		$form['email'] = 'test@email.com';
		$crawler = $client->submit($form);
		
		$this->assertTrue( $client->getResponse()->isRedirect('/login') , $client->getResponse()->getContent());
		
		$users = $this->em->getRepository('CWUserBundle:User')->findAll();
		$this->assertEquals(1,count($users));
		$user = $users[0];
		$this->assertEquals('Lucas', $user->getUsername());
		$this->assertEquals('test@email.com', $user->getEmail());
		$this->assertTrue($user->getIsActive());
		
		$encoder = static::$kernel->getContainer()->get('security.password_encoder');
		$this->assertTrue($encoder->isPasswordValid($user,'pass1',null));
	}
	
}

?>