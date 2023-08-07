<?php

namespace ADT\BackgroundQueue;

use Exception;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\Container;

class BackgroundQueueRabbitMQ
{
	const NOOP = 'noop';

	const PRODUCER_GENERAL = 'general';

	private Container $container;

	private BackgroundQueue $backgroundQueue;

	/** @var Producer[] */
	private array $producers = [];

	public function __construct(Container $container, BackgroundQueue $backgroundQueue)
	{
		$this->container = $container;
		$this->backgroundQueue = $backgroundQueue;
	}

	public function publish(int $id, ?string $producer = null): void
	{
		$this->doPublish($producer ?: self::PRODUCER_GENERAL, $id);
	}

	public function publishNoop(): void
	{
		$this->doPublish(self::PRODUCER_GENERAL, self::NOOP);
	}

	/**
	 * @throws Exception
	 */
	public function process(AMQPMessage $message): bool
	{
		$body = $message->getBody();

		if ($body === self::NOOP) {
			return true;
		}

		$this->backgroundQueue->process((int) $body);

		// vždy označit zprávu jako provedenou (smazat ji z rabbit DB)
		return true;
	}

	private function doPublish($producer, $id)
	{
		$this->getProducer($producer)->publish(self::NOOP);
	}

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
