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
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class CreateNotificationCommand extends Command
{
    private $em;
    private $userGroupRepository;
    private $notificationManager;
    private $router;
    protected static $defaultName = 'app:create-notification';

    public function __construct(
        NotificationManager $notificationManager,
        EntityManagerInterface $em,
        UserGroupRepository $userGroupRepository,
        RouterInterface $router)
    {
        $this->em = $em;
        $this->userGroupRepository = $userGroupRepository;
        $this->notificationManager = $notificationManager;
        $this->router = $router;
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
            ->addOption('route-name', 'rn', InputOption::VALUE_REQUIRED, "Path of target url, example: --rn=account_show.")
            ->addOption('route-parameters', 'rp', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Parameters of target url (in order) as array, example: --rp=20 --rp=43.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = $input->getArgument('content');
        $userGroupName = $input->getArgument('usergroup-name');
        $userGroupId = $input->getOption('usergroup-id');
        $routeName = $input->getOption('route-name');
        $routeParameters = $input->getOption('route-parameters');

        if ($routeName) {
            $routes = $this->router->getRouteCollection();
            // check if path exists
            if ($route = $routes->get($routeName)) {
                $path = $route->getPath();
                // prepare array of parameter keys for url
                preg_match_all('/{(.*?)}/', $path,$parameterKeys);
                $routeParameters = array_combine($parameterKeys[1], $routeParameters);
                // since we have path and parameters, we can try to generate url
                try {
                    $this->router->generate($routeName, $routeParameters);
                } catch (RouteNotFoundException $e) {
                    $output->writeln("<error>Url parameters $routeParameters are not valid.</error>");
                    return;
                }
            }

            else {
                $output->writeln("<error>Path $routeName not found.</error>");
                return;
            }
        }

        if ($userGroupId) {
            $userGroup = $this->userGroupRepository->findOneBy(['id' => $userGroupId]);

            if ($routeName) {
                $this->notificationManager->createNotification($userGroup, $content, $routeName, $routeParameters);
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

                if ($routeNameurl) {
                    $this->notificationManager->createNotification($userGroup, $content, $routeName, $routeParameters);
                }

                else {
                    $this->notificationManager->createNotification($userGroup, $content);
                }

                $output->writeln("Sent notification: $content to $userGroupName.");
            }

            elseif ($userGroupsCount > 1) {
                $output->writeln("<error>There are multiple groups with name $userGroupName. You need to provide group id.</error>");
            }

            else {
                $output->writeln("<error>Usergroup $userGroupName not found.</error>");
            }
        }
    }
}
