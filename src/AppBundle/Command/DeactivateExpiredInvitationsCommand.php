<?php
namespace AppBundle\Command;

use AppBundle\Service\GroupInvitationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeactivateExpiredInvitationsCommand extends Command
{
    private $groupInvitationManager;
    protected static $defaultName = 'app:deactivate-expired-invitations';

    public function __construct(GroupInvitationManager $groupInvitationManager)
    {
        $this->groupInvitationManager = $groupInvitationManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:deactivate-expired-invitations')
            ->setDescription('Deactivates expired invitations.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->groupInvitationManager->deactivateExpiredInvitations();
        $output->writeln("Done.");
    }
}
