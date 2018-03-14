<?php
namespace AppBundle\Command;

use AppBundle\Repository\UserGroupRepository;
use AppBundle\Service\NotificationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('content', InputArgument::REQUIRED, 'Contents of notification.')
            ->addArgument('usergroup-name', InputArgument::OPTIONAL, 'User group name - optional when you provide usergroup-id.')
            ->addOption('usergroup-id', 'id', InputOption::VALUE_REQUIRED, 'User group id - required when there are more groups with the same name.')
            ->addOption('url', 'url', InputOption::VALUE_REQUIRED, "Path and id of target url like this: 'path,id' , example: account_show,4.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = $input->getArgument('content');
        $userGroupName = $input->getArgument('usergroup-name');
        $userGroupId = $input->getOption('usergroup-id');
        $url = explode(',', $input->getOption('url'));
        $url = ['path' => $url[0], 'id' => $url[1]];

        if ($userGroupId) {
            $userGroup = $this->userGroupRepository->findOneBy(['id' => $userGroupId]);

            if ($url) {
                $this->notificationManager->createNotification($userGroup, $content, $url);
            }

            else {
                $this->notificationManager->createNotification($userGroup, $content);
            }

            $output->writeln("Sent notification: $content to user with id: $userGroupId.");
        }

        else {

            $userGroupsCount = $this->userGroupRepository->getCountByName($userGroupName);

            if ($userGroupsCount == 1) {
                $userGroup = $this->userGroupRepository->findOneBy(['name' => $userGroupName]);

                if ($url) {
                    $this->notificationManager->createNotification($userGroup, $content, $url);
                }

                else {
                    $this->notificationManager->createNotification($userGroup, $content);
                }

                $output->writeln("Sent notification: $content to $userGroupName.");
            }

            elseif ($userGroupsCount > 1) {
                $output->writeln("There are multiple groups with name $userGroupName. You need to provide group id.");
            }

            else {
                $output->writeln("Usergroup $userGroupName not found.");
            }
        }
    }
}
