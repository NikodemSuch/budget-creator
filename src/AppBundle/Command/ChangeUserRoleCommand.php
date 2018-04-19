<?php
namespace AppBundle\Command;

use AppBundle\Service\UserManager;
use AppBundle\Enum\UserRole;
use AppBundle\Exception\UserNotFoundException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ChangeUserRoleCommand extends Command
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
            ->setName(self::$defaultName)
            ->setDescription('Changes role of user.')
            ->addArgument('username', InputArgument::REQUIRED, 'The username or email of the user.')
            ->addArgument('newRole', InputArgument::REQUIRED, 'New role for user. Possible options: "ROLE_USER", "ROLE_ADMIN".')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $newRole = $input->getArgument('newRole');

        try {
            $newRole = new UserRole($newRole);
            $this->userManager->changeRole($username, $newRole);
            $output->writeln("Successfully changed role for user: $username");
        } catch (\UnexpectedValueException $e) {
            $output->writeln('<error>Invalid argument, possible options: "ROLE_USER", "ROLE_ADMIN".</error>');
        } catch (UserNotFoundException $e) {
            $output->writeln($e->getMessage());
        }
    }
}
