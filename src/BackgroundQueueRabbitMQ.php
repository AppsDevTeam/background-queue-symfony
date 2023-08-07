<?php

namespace ADT\BackgroundQueue;

use Exception;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use PhpAmqpLib\Message\AMQPMessage;
use Tracy\Debugger;
use Tracy\ILogger;

class BackgroundQueueRabbitMQ
{
	const NOOP = 'noop';

	const PRODUCER_GENERAL = 'general';

	private Producer $connection;

	private BackgroundQueue $backgroundQueue;

	public function __construct(AbstractConnection $connection, BackgroundQueue $backgroundQueue)
	{
		$this->connection = $connection;
		$this->backgroundQueue = $backgroundQueue;
	}

	public function publish(int $id, ?string $producer = null): void
	{
		$this->connection->getConnection()getProducer($producer ?: self::PRODUCER_GENERAL)->publish($id);
	}

	public function publishNoop(): void
	{
		try {
			$this->connection->getProducer(self::PRODUCER_GENERAL)->publish(self::NOOP);
		} catch (Exception $e) {
			Debugger::log($e, ILogger::EXCEPTION);
		}
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
}
