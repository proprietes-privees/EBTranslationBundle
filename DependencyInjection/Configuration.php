<?php

namespace EB\TranslationBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('eb_translation');

        $children = $rootNode->addDefaultsIfNotSet()->children();
        $children->scalarNode('domain')->defaultValue('messages')->info('Default translation domain')->example('messages');
        $children->scalarNode('locale')->defaultValue('%locale%')->info('Default translation locale')->example('%locale%');
        $children->booleanNode('useRouteAsClass')->defaultFalse()->info('Add route name as class when generating a link')->example('true');
        $children->booleanNode('replaceUnderscore')->defaultTrue()->info('Replace underscores by point in route names when generating a translation')->example('false');
        $children->scalarNode('trackSelectedLinks')->defaultNull()->info('Selected links class')->example('active');
        $children->scalarNode('prefix')->defaultValue('page.')->info('Translations prefix')->example('page.');

        return $treeBuilder;
    }
}
