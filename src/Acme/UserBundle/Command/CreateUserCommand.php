<?php

namespace Acme\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Acme\UserBundle\Entity\User;

class CreateUserCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('cw:user:create')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
            ));
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username   = $input->getArgument('username');
        $email      = $input->getArgument('email');
        $password   = $input->getArgument('password');

        $u = new User();
        $u->setUsername($username);
        $u->setEmail($email);

        $factory = $this->getContainer()->get('security.encoder_factory');
        $hash = $factory->getEncoder($u)->encodePassword($password, $u->getSalt());

        $u->setPassword($hash);
        $u->setIsActive(true);

        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->persist($u);
        $em->flush();

        $output->writeln(sprintf('Created user >%s', $username));
    }

}
