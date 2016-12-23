<?php

namespace app\commands;

use yii\console\Controller;
use ReflectionClass;
use Yii;

/**
 * Handles processing of Queue Events
 */
class QueueController extends Controller
{
    /**
     * Main action to call for queue scanning
     * @param $queueName    Defaults to the app queue
     */
    public function actionIndex($queueName = 'app')
    {
        $queue = Yii::$app->queue->get()->queue($queueName);
        while (true) {
            try {
                $job = $queue->pull(1000);
                if (is_null($job)) {
                    // There's nothing presently in the queue, so don't do anything
                    if (YII_DEBUG) {
                        Yii::info("There aren't any jobs in the queue. Waiting for additional jobs...");
                    }
                    continue;
                }
            } catch (\Disque\Queue\JobNotAvailableException $e) {
                if (YII_DEBUG) {
                    Yii::info("[" . date('Y-m-d H:i:s') . "] A scheduled job is available, but we should wait before running it.");
                }
                continue;
            }
            
            $body = $job->getBody();
            if (!isset($body['class'])) {
                Yii::error("An error occured when trying to run an event with the following payload:" . print_r($body, true));
                continue;
            }

            try {
                $class = $body['class'];
                unset($body['class']);

                $event = new $class($body, $queue, $job);
                $reflect = new ReflectionClass($event);
                $eventName = $reflect->getShortName();
                
                // Bind an event handler
                $this->on($eventName, function ($event) {
                    $event->run();
                    // The event should be handled in some way, if it isn't, called the global event handler and mark it as processed
                    if (!$event->handled) {
                        $event->handled();
                    }
                });

                // Trigger the event
                $this->trigger($eventName, $event);

                // Unbind the event handler
                $this->off($eventName);
            } catch (\Exception $e) {
                Yii::error("Event $class failed encountered an error: " . $e->getMessage());
                $queue->processed($job);
                continue;
            }
        }
    }
}