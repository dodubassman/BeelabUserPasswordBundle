<?php

namespace Beelab\UserPasswordBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}.
 */
class BeelabUserPasswordExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('beelab_user.password_min_length', $config['password_min_length']);
        $container->setParameter('beelab_user.password_reset_class', $config['password_reset_class']);
        $container->setParameter('beelab_user.password_reset_form_type', $config['password_reset_form_type']);
        $container->setParameter('beelab_user.new_password_form_type', $config['new_password_form_type']);
        $container->setParameter('beelab_user.email_parameters', $config['email_parameters']);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }
}
