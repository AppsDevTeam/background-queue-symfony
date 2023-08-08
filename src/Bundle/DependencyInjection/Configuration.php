<?php

namespace ADT\BackgroundQueueSymfony\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
	public function getConfigTreeBuilder(): TreeBuilder
	{
		$treeBuilder = new TreeBuilder('background_queue');

		$treeBuilder->getRootNode()
			->children()
				->arrayNode('callbacks')
					->isRequired()
					->arrayPrototype()
						->scalarPrototype()->end()
					->end()
				->end()
				->integerNode('notifyOnNumberOfAttempts')
					->isRequired()
					->min(1)
				->end()
				->scalarNode('tempDir')
					->isRequired()
				->end()
				->variableNode('connection')
					->validate()
						->ifTrue(function ($value) {
							return !is_string($value) && !is_array($value);
						})
						->thenInvalid('The "connection" must be either a string or an array.')
					->end()
				->end()
				->scalarNode('queue')
					->defaultValue('general')
				->end()
				->scalarNode('tableName')
					->defaultValue('background_job')
				->end()
				->scalarNode('logger')
					->defaultNull()
				->end()
				->variableNode('producer')
					->defaultNull()
				->end()
				->scalarNode('waitingQueue')
					->defaultNull()
				->end()
			->end();

		return $treeBuilder;
	}
}