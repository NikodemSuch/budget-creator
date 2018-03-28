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
            ->addOption('url', 'url', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, "Path and parameters of target url (in order) as array, example: --url=account_show --url=20.")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $content = $input->getArgument('content');
        $userGroupName = $input->getArgument('usergroup-name');
        $userGroupId = $input->getOption('usergroup-id');
        $url = $input->getOption('url');

        if ($url) {
            $urlPath = $url[0];
            unset($url[0]);
            // since path is unset, rest of elements are url parameters
            $urlParameters = $url;
            $routes = $this->router->getRouteCollection();

            // check if path exists
            if ($routes->get($urlPath)) {
                $route = $routes->get($urlPath);
                $path = $route->getPath();

                // prepare array of parameter keys for url
                preg_match_all('/{(.*?)}/', $path,$parameterKeys);
                $urlParameters = array_combine($parameterKeys[1], $urlParameters);

                // since we have path and parameters, we can try to generate url
                try {
                    $this->router->generate($urlPath, $urlParameters);
                } catch (RouteNotFoundException $e) {
                    $output->writeln("Url parameters $urlParameters are not valid.");
                    return;
                }
            }

            else {
                $output->writeln("Path $urlPath not found.");
                return;
            }
        }


        if ($userGroupId) {
            $userGroup = $this->userGroupRepository->findOneBy(['id' => $userGroupId]);

            if ($url) {
                $this->notificationManager->createNotification($userGroup, $content, $urlPath, $urlParameters);
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
                    $this->notificationManager->createNotification($userGroup, $content, $urlPath, $urlParameters);
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
