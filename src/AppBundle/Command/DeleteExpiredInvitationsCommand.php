<?php
namespace AppBundle\Command;

use AppBundle\Service\GroupInvitationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteExpiredInvitationsCommand extends Command
{
    private $groupInvitationManager;
    protected static $defaultName = 'app:delete-expired-invitations';

    public function __construct(GroupInvitationManager $groupInvitationManager)
    {
        $this->groupInvitationManager = $groupInvitationManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:delete-expired-invitations')
            ->setDescription('Deletes expired invitations.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->groupInvitationManager->deleteExpiredInvitations();
        $output->writeln("Done.");
    }
}
