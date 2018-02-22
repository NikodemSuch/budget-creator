<?php
namespace AppBundle\Command;

use AppBundle\Service\UserManager;
use AppBundle\Exception\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeUserRole extends Command
{
    private $userManager;
    protected static $defaultName = 'app:change-role';

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:change-role')
            ->setDescription('Changes role of user.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username or email of the user.')
            ->addArgument('newRole', InputArgument::REQUIRED, 'New role for user. Possible options: "user", "admin".')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $newRole = $input->getArgument('newRole');

        try {
            $this->userManager->changeRole($username, $newRole);
            $output->writeln('Succesfuly changed role for user: '.$username);
        } catch (UserNotFoundException $e) {
            $output->writeln($e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
