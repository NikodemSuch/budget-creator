<?php
namespace AppBundle\Command;

use AppBundle\Repository\UserGroupRepository;
use AppBundle\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class CreateNotificationCommand extends Command
{
    private $em;
    private $userGroupRepository;
    private $notificationManager;
    protected static $defaultName = 'app:create-notification';

    public function __construct(NotificationManager $notificationManager, EntityManagerInterface $em, UserGroupRepository $userGroupRepository)
    {
        $this->em = $em;
        $this->userGroupRepository = $userGroupRepository;
        $this->notificationManager = $notificationManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:create-notification')
            ->setDescription('Creates notification.')
            ->addArgument('usergroup-name', InputArgument::REQUIRED, 'User group name.')
            ->addArgument('content', InputArgument::REQUIRED, 'Contents of notification.')
            ->addArgument('usergroup-id', InputArgument::OPTIONAL, 'User group id - required when there are more groups with the same name.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $userGroupName = $input->getArgument('usergroup-name');
        $content = $input->getArgument('content');
        $userGroupId = $input->getArgument('usergroup-id');

        $userGroupsCount = $this->userGroupRepository->getCountByName($userGroupName);

        if ($userGroupsCount == 1) {
            $userGroup = $this->userGroupRepository->findOneBy([
                'name' => $userGroupName
            ]);
            $this->notificationManager->createNotification($userGroup, $content);
            $output->writeln("Sent notification: $content to $userGroupName.");
        }

        else if ($userGroupsCount > 1 && $userGroupId) {
            $userGroup = $this->userGroupRepository->findOneBy([
                'id' => $userGroupId,
                'name' => $userGroupName
            ]);

            if ($userGroup) {
                $this->notificationManager->createNotification($userGroup, $content);
                $output->writeln("Sent notification: $content to $userGroupName.");
            }
        }

        else if ($userGroupsCount > 1) {
            $output->writeln("There are multiple groups with name $userGroupName. You need to provide group id.");
        }

        else {
            $output->writeln("Usergroup $userGroupName not found.");
        }
    }
}
