# Events & Message Queues

The API provides support for asyncronous events thought [Disque](https://github.com/antirez/disque), and takes advantage of the Yii Event class.

# Disque

## Setting up Disque

See [Disque](https://github.com/antirez/disque) for information on how to install Disque onto your system. For your convenience, the provided `Vagrant` box will install and start Disque for you.

## Application Configuration

The configuring for `Disque` is located in `config/config.yml` under the `Disque` section. By default the following `clients` section is provided.

```yaml
disque: 
  clients: 
    - 
      host: "127.0.0.1"
      password: null
      port: 7711
```

If you're using a larger cluster, simply add any extra clients to the configuration file.

## Running the Queue

Once the application is properly configured, you can launch the default queue by running the following Yii2 command line task:

```bash
./yii queue/index
```

By default the `app` queue will be started. If you're application uses multiple queues, you can start them by passing the first paramter to `queue/index`.

```bash
./yii queue/index myQueueName
```

Events will be handled and processed as they arrive. Informational and error logging will be redirected to `runtime/logs/app.log`, or whatever standard error logging you have in place.

# Queuing Events

Events can be queued by through the `Yii::$app->queue` component. At minimum, the following signature is required:

```php
Yii::$app->queue->addJob([
    'class' => '\app\events\MyEvent'
]);
```

If you want to add a job to a different queue, set the second parameter to the queue name.

```php
Yii::$app->queue->addJob([
    'class' => '\app\events\MyEvent'
], 'myQueueName');
```

Additional attributes can be added to job. These attributes should map to properties outlined in the selected class.

```php
Yii::$app->queue->addJob([
    'class' => '\app\events\MyEvent'
    'property1' => 'val',
    'property2' => $obj,
    'property3' => [
        'array' => [
            'of' => [
                'stuff'
            ]
        ]
    ]
]);
```

# Creating Event Handlers

Events extend from `yrc\events\AbstractEvent`, which extends from the standard Yii2 `yii\base\Event` class with one minor difference - they contain their own task runner. (For a full example of this, see `yrc\events\SendEmailEvent` in `vendor/charlesportwoodii/yii2-api-rest-components/events/SendEmailEvent.php`).

Events should declare all properties they contain, and a method called `run()`, which should return true or false. An simple example is shown below:

```php
<?php

namespace app\events;

use Yii;

final class MyEvent extends \yrc\events\AbstractEvent
{
    public $property1;
    public $property2;
    public $property3;

    public function run()
    {
        return $this->handled();
    }
}
```

The `run()` function _must_ return a boolean `true` or `false`, and _should_ declare that it was handled through calling of `$this->handled()`, or `$this->retry()`.