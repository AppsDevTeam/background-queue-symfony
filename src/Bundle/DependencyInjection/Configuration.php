<?php

namespace ADT\BackgroundQueueSymfony\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\Reference;

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
					->validate()
						->always(function ($value) {
							foreach ($value as $_callback) {
								$this->checkCallback($_callback);
							}

							return $value;
						})
					->end()
				->end()
				->integerNode('notifyOnNumberOfAttempts')
					->isRequired()
					->min(1)
				->end()
				->scalarNode('tempDir')
					->isRequired()
				->end()
				->scalarNode('locksDir')
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
					->isRequired()
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
				->scalarNode('waitingJobExpiration')
					->defaultValue(1000)
				->end()
				->variableNode('onBeforeProcess')
					->defaultNull()
					->validate()
						->always(function ($value) {
							$this->checkCallback($value);

							return $value;
						})
					->end()
				->end()
				->variableNode('onError')
					->defaultNull()
					->validate()
						->always(function ($value) {
							$this->checkCallback($value);

							return $value;
						})
					->end()
				->end()
				->variableNode('onAfterProcess')
					->defaultNull()
					->validate()
						->always(function ($value) {
							$this->checkCallback($value);

							return $value;
						})
					->end()
				->end()
			->end();

		return $treeBuilder;
	}

	private function checkCallback($callback)
	{
		if ($callback[0][0] === '@') {
			$callback[0] = substr($callback[0], 1);
			$callback[0] = (string) new Reference($callback[0]);
		}

		if (!is_callable($callback)) {
			throw new InvalidConfigurationException('Callback "' . $callback[0] . '::' . $callback[1] . '" does not exist or is not callable.');
		}
	}
}