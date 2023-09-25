<?php

namespace ADT\BackgroundQueueSymfony\Bundle\DependencyInjection;

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

		foreach ($config['callbacks'] as &$_callback) {
			$_callback[0] = $this->normalizeClass($_callback[0]);
		}
		if ($config['producer']) {
			$config['producer'] = $this->normalizeClass($config['producer']);
			$loader->load('broker.yml');
		}
		if ($config['logger']) {
			$config['logger'] = $this->normalizeClass($config['logger']);
		}
		if ($config['onBeforeProcess']) {
			$config['onBeforeProcess'][0] = $this->normalizeClass($config['onBeforeProcess'][0]);
		}
		if ($config['onError']) {
			$config['onError'][0] = $this->normalizeClass($config['onError'][0]);
		}
		if ($config['onAfterProcess']) {
			$config['onAfterProcess'][0] = $this->normalizeClass($config['onAfterProcess'][0]);
		}

		$definition = new Definition('ADT\BackgroundQueue\BackgroundQueue', [$config]);
		$container->setDefinition('ADT\BackgroundQueue\BackgroundQueue', $definition);

		$services = $container->findTaggedServiceIds('background-queue.command');

		foreach ($services as $id => $tags) {
			$definition = $container->getDefinition($id);

			// Zavolejte metodu setLocksDir na každou službu
			$definition->addMethodCall('setLocksDir', [$config['locksDir']]);
		}
	}

	private function normalizeClass(string $class)
	{
		if ($class[0] === '@') {
			$class = new Reference(substr($class, 1));
		}
		return $class;
	}
}