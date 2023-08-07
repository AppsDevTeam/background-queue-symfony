<?php

namespace ADT\BackgroundQueue\Bundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class BackgroundQueueExtension extends Extension
{
	/**
	 * @throws Exception
	 */
	public function load(array $configs, ContainerBuilder $container)
	{
		$configuration = new Configuration();
		$config = $this->processConfiguration($configuration, $configs);

		$loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
		$loader->load('services.yaml');

		foreach ($config['callbacks'] as &$callback) {
			$callback[0] = new Reference(substr($callback[0], 1)); // removes @;
		}
		if ($config['amqpPublishCallback']) {
			$config['amqpPublishCallback'][0] =  new Reference(substr($config['amqpPublishCallback'][0], 1)); // removes @;
		}

		$definition = new Definition('ADT\BackgroundQueue\BackgroundQueue', [$config]);
		$container->setDefinition('ADT\BackgroundQueue\BackgroundQueue', $definition);
	}
}