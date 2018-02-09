<?php

namespace AppBundle\Command;

use AppBundle\Service\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class CreateUserCommand extends Command
{
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
		->addArgument('username', InputArgument::REQUIRED, 'The username of the user.')
		->addArgument('email', InputArgument::REQUIRED, 'Email of the user.')
		->addArgument('password', InputArgument::REQUIRED, 'Account password.')
		;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$username = $input->getArgument('username');
		$email = $input->getArgument('email');
		$plainPassword = $input->getArgument('password');

		try {
			$this->userManager->createUser($username, $email, $plainPassword);

			$output->writeln([
				'Following User has been created:',
				'============',
				'Username: '.$username,
				'Email: '.$email,
			]);
		} catch(UniqueConstraintViolationException $e)  {
			$output->writeln('Username already taken.');
		}
	}
}
