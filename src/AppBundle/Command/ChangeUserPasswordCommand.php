<?php
namespace AppBundle\Command;

use AppBundle\Service\UserManager;
use AppBundle\Exception\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeUserPasswordCommand extends Command
{
    private $userManager;
    protected static $defaultName = 'app:change-password';

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:change-password')
            ->setDescription('Changes password of user.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username or email of the user.')
            ->addArgument('newPassword', InputArgument::REQUIRED, 'New password for account.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $newPlainPassword = $input->getArgument('newPassword');

        try {
            $this->userManager->changePassword($username, $newPlainPassword);
            $output->writeln('Succesfuly changed password for user: '.$username);
        } catch (UserNotFoundException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
