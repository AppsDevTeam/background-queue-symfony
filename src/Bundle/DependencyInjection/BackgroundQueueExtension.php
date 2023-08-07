<?php

namespace App\BackgroundQueue\Bundle\DependencyInjection;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Definition;

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

		$definition = new Definition('ADT\BackgroundQueue\BackgroundQueue', [$config]);
		$container->setDefinition('ADT\BackgroundQueue\BackgroundQueue', $definition);
	}
}