<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Game;

class GameController extends Controller
{
    /**
     * @Route("/games/create", name="create_game")
     */
    public function createAction()
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $game = new Game();
        $game->setNumWords(0);
        $game->setLastWord('');
        $game->setUser($user->getUsername());
        $game->setCreationDate(new \DateTime());
        $game->setWinned(false);

        $em = $this->getDoctrine()->getManager();

        $em->persist($game);
        $em->flush();

        $response = new Response();
        return $response;
    }
}
