<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Game;
use AppBundle\Entity\GameWord;

class GameController extends Controller
{

    /**
     * @Route("/games", name="games_index")
     */
    public function indexAction() {
        return $this->render('games/index.html.twig');
    }

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

    /**
     * @Route("/games/play", name="play_game")
     */
    public function playAction(){
        $request = $this->getRequest();
        $response = new Response();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $word = $request->query->get('word');

        // get current game
        $game = $em->getRepository('AppBundle:Game')->findOneBy(array(
            'endDate' => null,
            'user' => $user->getUsername(),
        ));
        if (!isset($game)) {
            $response->setStatusCode(404, 'Game not found');
            return $response;
        }

        $usedWordsDB = $em->getRepository('AppBundle:GameWord')->findBy(array(
            'gameId' => $game->getId(),
        ));

        $usedWords = array();
        foreach ($usedWordsDB as $uwDB) {
            $usedWords[] = $uwDB->getWord();
        }

        // check word not used
        if (in_array($word, $usedWords)) {
           $game->setEndDate(new \DateTime());
           $em->flush();
           $response->setStatusCode(451, 'Word already used');
           return $response;
        }

        // check word is valid
        $lastWord = $game->getLastWord();
        if (!empty($lastWord)) {
            $lastWordEnd = substr($lastWord,-2);
            $wordStart = substr($word,0,2);

            if ($lastWordEnd != $wordStart) {
                $game->setEndDate(new \DateTime());
                $em->flush();
                $response->setStatusCode(452, 'Word is not valid');
                return $response;
            }
        }

        // check word exists (and get new word)
        $wordEnd = substr($word,-2);
        $dict = file_get_contents('dict.txt');
        $dictWords = explode("\n", $dict);

        $found = false;
        $newWord = false;

        // TODO probably can be optimized with the database
        foreach ($dictWords as $dictWord) {
           if ($dictWord == $word){
               $found = true;
           } else {
               $dictWordStart = substr($dictWord, 0, 2);
               if (empty($newWord) && $dictWordStart == $wordEnd && !in_array($dictWord, $usedWords)) {
                   $newWord = $dictWord;
               }
           }

           if ($found && !empty($newWord)){
               break;
           }
        }

        if (!$found) {
           $game->setEndDate(new \DateTime());
           $em->flush();
           $response->setStatusCode(453, 'Word is not found');
           return $response;
        }

        $result = array();

        if (empty($newWord)) {
           $result['winned'] = true;

           $game->setLastWord($word);
           $game->setEndDate(new \DateTime());
           $game->setWinned(true);
           $game->setNumWords( $game->getNumWords() + 1 );

           $gw = new GameWord();
           $gw->setGameId($game->getId());
           $gw->setWord($word);
           $em->persist($gw);

        } else {
           $result['winned'] = false;
           $result['last_word'] = $newWord;
           $game->setLastWord($newWord);
           $game->setNumWords( $game->getNumWords() + 2 );

           $gw = new GameWord();
           $gw->setGameId($game->getId());
           $gw->setWord($word);
           $em->persist($gw);

           $gw = new GameWord();
           $gw->setGameId($game->getId());
           $gw->setWord($newWord);
           $em->persist($gw);
        }
        $em->flush();

        $response->setContent(json_encode($result));
        return $response;
    }

}
