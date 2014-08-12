<?php

namespace ES\Bundle\BaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
	static public $globals = array(
		'host_env',
		'project_url',
		'project_name',
		'project_title',
	);

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('es_base');

		$supportedAuthTypes = array('form', 'basic');
		$supportedDrivers = array('orm', 'mongodb');

		$children = $rootNode
			->children();

		foreach (self::$globals as $key) {
			$children->scalarNode($key)->defaultValue('%cameleon.' . $key . '%')->cannotBeEmpty()->end();
		}

		$children
				->scalarNode('db_driver')
					->defaultValue('orm')
                    ->validate()
                        ->ifNotInArray($supportedDrivers)
                        ->thenInvalid('The driver %s is not supported. Please choose one of '.json_encode($supportedDrivers))
                    ->end()
                    ->cannotBeOverwritten()
                    ->cannotBeEmpty()
                ->end()
				->arrayNode('templating')
					->addDefaultsIfNotSet()
					->children()
						->arrayNode('bootstrap')
							->addDefaultsIfNotSet()
							->children()
								->scalarNode('use_cdn')->defaultTrue()->end()
							->end()
						->end()
					->end()
				->end()
				->arrayNode('google_analytics')
					->children()
						->scalarNode('website_name')->defaultValue('%cameleon.project_name%')->end()
						->arrayNode('trackers')
							->example('main: UA-47067754-2')
							->useAttributeAsKey('name')
							->prototype('scalar')
							->end()
						->end()
						->arrayNode('tracked_environments')
							->prototype('scalar')->end()
							->defaultValue(array('prod'))
						->end()
					->end()
				->end()
				->arrayNode('staging')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('encoder')->defaultValue('plaintext')->end()
						->scalarNode('password')->defaultNull()->end()
						->scalarNode('invite')->defaultValue('staging.auth.title')->end()
						->arrayNode('users')
							->useAttributeAsKey('name')
							->prototype('array')
								->children()
									->scalarNode('name')->end()
									->scalarNode('password')->isRequired()->end()
									->arrayNode('roles')
										->beforeNormalization()->ifString()->then(function ($v) { return preg_split('/\s*,\s*/', $v); })->end()
										->prototype('scalar')->end()
										->defaultValue(array('ROLE_STAGING'))
									->end()
								->end()
							->end()
						->end()
						->arrayNode('secured_environments')
							->prototype('scalar')->end()
							->defaultValue(array('staging'))
						->end()
						->arrayNode('allowed_path')
							->prototype('scalar')->end()
							->defaultValue(array())
						->end()
						->arrayNode('allowed_user_agent')
							->prototype('scalar')->end()
							->defaultValue(array('^facebookexternalhit.*'))
						->end()
						->scalarNode('authtype')
							->validate()
								->ifNotInArray($supportedAuthTypes)
								->thenInvalid('The auth_type %s is not supported. Please choose one of ' . json_encode($supportedAuthTypes))
							->end()
							->cannotBeEmpty()
							->defaultValue('form')
						->end()
						->arrayNode('authtype_options')
							->prototype('scalar')->end()
							->defaultValue(array())
						->end()
					->end()
				->end()
			->end()
		;

        return $treeBuilder;
    }
}
