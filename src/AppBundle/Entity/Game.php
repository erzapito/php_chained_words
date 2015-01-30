<?php

// src/Acme/UserBundle/Entity/User.php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * AppBundle\Entity\Game
 *
 * @ORM\Table(name="game")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\GameRepository")
 */
class Game
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $numWords;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $winned;

    /**
     * @ORM\Column(type="string")
     */
    private $lastWord;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creationDate;
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;



    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param string $userId
     * @return Game
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set numWords
     *
     * @param integer $numWords
     * @return Game
     */
    public function setNumWords($numWords)
    {
        $this->numWords = $numWords;

        return $this;
    }

    /**
     * Get numWords
     *
     * @return integer
     */
    public function getNumWords()
    {
        return $this->numWords;
    }

    /**
     * Set winned
     *
     * @param boolean $winned
     * @return Game
     */
    public function setWinned($winned)
    {
        $this->winned = $winned;

        return $this;
    }

    /**
     * Get winned
     *
     * @return boolean
     */
    public function getWinned()
    {
        return $this->winned;
    }

    /**
     * Set lastWord
     *
     * @param $lastWord
     * @return Game
     */
    public function setLastWord($lastWord)
    {
        $this->lastWord = $lastWord;

        return $this;
    }

    /**
     * Get lastWord
     *
     * @return \varchar
     */
    public function getLastWord()
    {
        return $this->lastWord;
    }

    /**
     * Set creationDate
     *
     * @param \DateTime $creationDate
     * @return Game
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Get creationDate
     *
     * @return \DateTime
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     * @return Game
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }
}
