<?php

/*
 * This file is part of the Cyclear-game package.
 *
 * (c) Erik Trapman <veggatron@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cyclear\GameBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cyclear_game');
        
        $rootNode->children()
            ->booleanNode('enable_twitter')->defaultFalse()->end()
			->scalarNode('max_transfers')->defaultNull()->end()
            ->end();
        
		/*
        $rootNode->children()
        	->arrayNode('cyclear_logger')
        		->children()
        			->arrayNode('handler')
        				->children()
        					->arrayNode('db')
        						->children()
	        						->booleanNode('enabled')->end()
        							->scalarNode('level')->end()
        						->end()
        					->end()
        					->arrayNode('mail')
        						->children()
        							->booleanNode('enabled')->end()
        							->scalarNode('level')->end()
        						->end()
        					->end()
        				->end()
        			->end()
        		->end()
        	->end()
        
        
        
        ->end();
        */
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
