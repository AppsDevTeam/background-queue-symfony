# Background Queue for Symfony using RabbitMQ

### 1.1 Installation

```
composer require adt/background-queue-symfony
```

### 1.2 Configuration

```php
return [
	ADT\BackgroundQueue\Bundle\BackgroundQueueBundle::class => ['all' => true]
];
```

```neon
background_queue:
	callbacks:
		sendEmail: [@App\Model\Mailer, sendEmail]
		...
	notifyOnNumberOfAttempts: 5 # počet pokusů o zpracování záznamu před zalogováním
	tempDir: %tempDir% # cesta pro uložení zámku proti vícenásobnému spuštění commandu
	queue: general # nepovinné, název fronty, do které se ukládají a ze které se vybírají záznamy
	connection: %database% # parametry predavane do Doctrine\Dbal\Connection
	amqpPublishCallback: ['@ADT\BackgroundQueue\BackgroundQueueRabbitMQ', 'publish'] # nepovinné, callback, který publishne zprávu do brokera
	amqpWaitingQueueName: 'waiting' # nepovinné, název queue, kam ukládat záznamy, které ještě nelze zpracovat
```

```neon
old_sound_rabbit_mq:
  connections:
    default:
      url: '%env(RABBITMQ_URL)%'

  producers:
    general:
      connection: default
      exchange_options: {name: '%env(RABBITMQ_NAME)%', type: direct}
      queue_options: {name: '%env(RABBITMQ_NAME)%', arguments: {'x-queue-type': ['S', 'quorum']}}

    waiting:
        connection: default
        exchange_options: {name: '%env(RABBITMQ_NAME)%_waiting', type: direct}
        queue_options: {name: '%env(RABBITMQ_NAME)%_waiting', arguments: {'x-dead-letter-exchange': ['S', %rabbitMQ.name%], 'x-message-ttl': ['I', 1000]}}

  consumers:
    general:
      connection: default
      exchange_options: {name: '%env(RABBITMQ_NAME)%, type: direct}
      queue_options: {name: '%env(RABBITMQ_NAME)%', arguments: {'x-queue-type': ['S', 'quorum']}}
      callback: ADT\BackgroundQueue\BackgroundQueueRabbitMQ
      # Consumery máme nastavené tak, že zpracují max 1 zprávu, takže nastavíme,
      # aby si jich více nezabíral.
      qos_options: {prefetch_count: 1}

    waiting:
        connection: default
        exchange_options: {name: '%env(RABBITMQ_NAME)%', type: direct}
        queue_options: {name: '%env(RABBITMQ_NAME)%', arguments: {'x-queue-type': ['S', 'quorum']}}
        callback: ADT\BackgroundQueue\BackgroundQueueRabbitMQ
        # Consumery máme nastavené tak, že zpracují max 1 zprávu, takže nastavíme,
        # aby si jich více nezabíral.
        qos_options: {prefetch_count: 1}
```
