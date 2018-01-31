<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\LockableTrait;
use AppBundle\Service\UserManager;

class CreateUserCommand extends Command
{
    use LockableTrait;
    private $userManager;
    protected static $defaultName = 'app:create-user';

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
          ->setName('app:create-user')
          ->setDescription('Creates a new user.')
          ->setHelp('This command allows you to create a user.')
          ->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
          ->addArgument('email', InputArgument::REQUIRED, 'Email of the user.')
          ->addArgument('password', InputArgument::REQUIRED, 'Password for your account.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');

            return 0;
        }

        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $plainPassword = $input->getArgument('password');

        $this->userManager->create($username, $email, $plainPassword);

        $output->writeln([
          'Following User has been created:',
          '============',
          '',
        ]);

        $output->writeln('Username: '.$username);
        $output->writeln('Email: '.$email);
        $output->writeln('Password: '.$plainPassword);
    }
}
