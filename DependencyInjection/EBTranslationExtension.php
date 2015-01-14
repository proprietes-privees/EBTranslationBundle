<?php

namespace EB\TranslationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EBTranslationExtension
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class EBTranslationExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load configuration
        $conf = $this->processConfiguration(new Configuration(), $configs);
        $container->setParameter('eb_translation.translation.domain', $conf['domain']);
        $container->setParameter('eb_translation.translation.locale', $conf['locale']);
        $container->setParameter('eb_translation.translation.use_route_as_class', $conf['use_route_as_class']);
        $container->setParameter('eb_translation.translation.replace_underscore', $conf['replace_underscore']);
        $container->setParameter('eb_translation.translation.track_selected_links', $conf['track_selected_links']);
        $container->setParameter('eb_translation.translation.prefix', $conf['prefix']);

        // Load services
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
    }
}
