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
  onBeforeProcess: ['@App\Model\Database', 'switchDatabase']
  onError: ['ADT\Utils\Guzzle', 'handleException']
  onAfterProcess: ['@App\Model\Database', 'switchDatabaseBack']
```

## 1.3 RabbitMQ (optional)

### 1.3.1 Installation

How to install RabbitMQ, check https://github.com/AppsDevTeam/background-queue

### 1.3.2 Configuration

```yaml
background_queue:
  producer: '@ADT\BackgroundQueue\Broker\AmqpLib\Producer'
  waitingJobExpiration: 1000
```

## 1.4 Documentation

https://github.com/AppsDevTeam/background-queue
