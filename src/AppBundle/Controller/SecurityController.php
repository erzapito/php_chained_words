<?php

// src/AppBundle/Controller/SecurityController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Acme\UserBundle\Entity\User;

class SecurityController extends Controller
{
	
	const MIN_PASSWORD_LENGTH = 4;
	const MAX_PASSWORD_LENGTH = 4096;
	
    /**
     * @Route("/login", name="login")
     */
    public function loginAction(Request $request)
    {
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'AppBundle::security/login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error'         => $error,
            )
        );
    }

    /**
     * @Route("/login_check", name="login_check")
     */
    public function loginCheckAction()
    {
    }
    
    /**
     * @Route("/registration", name="registration")
     */
    public function registrationAction(Request $request) {
    	$encoder = $this->container->get('security.password_encoder');
    	
    	$errors = array();
    	$username = $request->request->get('username');
    	$password = $request->request->get('password');
    	
    	// length validation
    	if (strlen($password) >= self::MAX_PASSWORD_LENGTH) {
    		$r = new Response();
    		$r->setStatusCode(400);
    		return $r;
    	}
    	
    	$repeatPassword = $request->request->get('repeat_password');
    	$email = $request->request->get('email');
    	if ($request->isMethod('POST')) {
    		
    		$errors = $this->validate($username,$password,$repeatPassword,$email);
    		
    		// create user
    		if (empty($errors)) {
    			
    			return $this->createUser($username,$password,$email);
    		}
    	}
    	return $this->render(
    			'AppBundle::security/registration.html.twig',
    			array(
    					'errors' => $errors,
    					'username' => $username,
    					'email' => $email,
    			)
    	);
    }
    
    private function createUser($username, $password, $email) {
    	$em = $this->getDoctrine()->getManager();
    	
    	$u = new User();
    	$u->setUsername($username);
    	$u->setIsActive(true);
    	$u->setEmail($email);
    	
    	$encoder = $this->container->get('security.password_encoder');
    	$u->setPassword($encoder->encodePassword($u, $password));
    	
    	$em->persist($u);
    	$em->flush();
    	
    	return $this->redirect(path('/login'));
    }
    
    private function validate ($username, $password, $repeatPassword, $email) {
    	$em = $this->getDoctrine()->getManager();
    	$errors = array();
    	
    	// check username is present
    	if (empty($username)){
    		$errors[] = 'Username is mandatory';
    	}
    	
    	// check email is present
    	if (empty($email)) {
    		$errors[] = 'Email is mandatory';
    	}
    	
    	// check passwords match
    	if ($password != $repeatPassword) {
    		$errors[] = 'Passwords don\'t match';
    	}
    	
    	// check password length
    	if (strlen($password) < self::MIN_PASSWORD_LENGTH) {
    		$errors[] = 'Password must be at least 4 characters long';
    	}
    	
    	// check password simple
    	if (!preg_match('/[0-9]/', $password)) {
    		$errors[] = 'Password should contain a number or a unicorn horn';
    	}
    	
    	// username in use
    	$prevUser = $em->getRepository('CWUserBundle:User')->findOneBy(array(
    			'username' => $username
    	));
    	if (isset($prevUser)) {
    		$errors[] = 'Username already in use';
    	}
    	
    	// email in use
    	$prevUser = $em->getRepository('CWUserBundle:User')->findOneBy(array(
    			'email' => $email
    	));
    	if (isset($prevUser)) {
    		$errors[] = 'Email already in use';
    	}
    	return $errors;
    }
}