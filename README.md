# Background Queue for Symfony using RabbitMQ

## 1.1 Installation

```
composer require adt/background-queue-symfony
```

## 1.2 Configuration

```php
return [
	ADT\BackgroundQueueSymfony\Bundle\BackgroundQueueBundle::class => ['all' => true]
];
```

```yaml
background_queue:
	callbacks:
		sendEmail: ['@App\Model\Mailer', 'sendEmail']
	notifyOnNumberOfAttempts: 5
	tempDir: %tempDir%
	connection: %database%
	queue: general
	logger: '@logger'
```

## 1.3 RabbitMQ (optional)

### 1.3.1 Installation

```
composer require php-amqplib/rabbitmq-bundle
```

To make sure you avoid BC break when upgrading `php-amqplib/rabbitmq-bundle` in the future, add to your composer:

```json
{
  "conflict": {
    "php-amqplib/rabbitmq-bundle": "<2.0.0 || >=3.0.0"
  }
}
```

### 1.3.2 Configuration

```php
return [
	OldSound\RabbitMqBundle\OldSoundRabbitMqBundle::class => ['all' => true]
];
```

This line must be after `BackgroundQueueBundle` line.

```yaml
background_queue:
	producer: '@ADT\BackgroundQueueSymfony\Broker\Producer'
	waitingQueue: 'waiting'
```

```yaml
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
      exchange_options: {name: '%env(RABBITMQ_NAME)%', type: direct}
      queue_options: {name: '%env(RABBITMQ_NAME)%', arguments: {'x-queue-type': ['S', 'quorum']}}
      callback: ADT\BackgroundQueueSymfony\Broker\Consumer
      # Consumery máme nastavené tak, že zpracují max 1 zprávu, takže nastavíme,
      # aby si jich více nezabíral.
      qos_options: {prefetch_count: 1}
```

## 1.4 Documentation

https://github.com/AppsDevTeam/background-queue
