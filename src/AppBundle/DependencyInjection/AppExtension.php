<?php

namespace AppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader( $container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $definition = $container->getDefinition('AppBundle\Service\GroupInvitationManager');
        $definition->addMethodCall('setExpirationTime', [$config['invitation_expiration_time']]);

        $definition = $container->getDefinition('AppBundle\Service\NotificationManager');
        $definition->addMethodCall('setVisibilityTime', [$config['notification_visibility_time']]);
    }
}
