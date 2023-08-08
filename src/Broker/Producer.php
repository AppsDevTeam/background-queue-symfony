<?php

namespace ADT\BackgroundQueueSymfony\Broker;

use Exception;
use Symfony\Component\DependencyInjection\Container;

class Producer implements \ADT\BackgroundQueue\Broker\Producer
{
	const NOOP = 'noop';

	const PRODUCER_GENERAL = 'general';

	private Container $container;

	/** @var \OldSound\RabbitMqBundle\RabbitMq\Producer[] */
	private array $producers = [];

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @throws Exception
	 */
	public function publish(int $id, ?string $queue = null): void
	{
		$this->doPublish($queue ?: self::PRODUCER_GENERAL, $id);
	}

	/**
	 * @throws Exception
	 */
	public function publishNoop(): void
	{
		$this->doPublish(self::PRODUCER_GENERAL, self::NOOP);
	}

	/**
	 * @throws Exception
	 */
	private function doPublish($producer, $id)
	{
		$this->getProducer($producer)->publish($id);
	}

	/**
	 * @throws Exception
	 */
	private function getProducer($producer)
	{
		if (!$this->producers) {
			foreach ($this->container->getServiceIds() as $serviceId) {
				if (strpos($serviceId, 'old_sound_rabbit_mq.') === 0 && strpos($serviceId, '_producer') !== false) {
					$index = str_replace(['old_sound_rabbit_mq.', '_producer'], '', $serviceId);
					$this->producers[$index] = $this->container->get($serviceId);
				}
			}
		}

		return $this->producers[$producer];
	}
}
