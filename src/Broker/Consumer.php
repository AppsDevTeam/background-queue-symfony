<?php

namespace ADT\BackgroundQueueSymfony\Broker;

use ADT\BackgroundQueue\BackgroundQueue;
use Exception;
use PhpAmqpLib\Message\AMQPMessage;

class Consumer
{
	const NOOP = 'noop';

	private BackgroundQueue $backgroundQueue;

	public function __construct(BackgroundQueue $backgroundQueue)
	{
		$this->backgroundQueue = $backgroundQueue;
	}

	/**
	 * @throws Exception
	 */
	public function execute(AMQPMessage $message): bool
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
