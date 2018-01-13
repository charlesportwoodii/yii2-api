<?php declare(strict_types=1);

namespace app\commands;

use Exception;
use yii\console\Controller;
use Yii;

final class WorkerController extends Controller
{
    /**
     * @var string
     * The RPQ Job ID as passed by the RPQ CLI
     */
    public $jobId;

    /**
     * @var string
     * The name of the RPQ queue that this command should search for jobs in.
     */
    public $name = 'default';

    /**
     * Command line options
     * @param string $actionID
     * @return array
     */
    public function options($actionID)
    {
        return [
            'jobId',
            'name'
        ];
    }

    /**
     * Starts a worker in process mode
     * @return integer
     */
    public function actionProcess()
    {
        $hash = explode(':', $this->jobId);
        $jobId = $hash[count($hash) - 1];

        try {
            $job = Yii::$app->rpq->getQueue($this->name)->getJob($jobId);
            $class = $job->getWorkerClass();

            if (!\class_exists($class)) {
                throw new Exception("Unable to find worker class {$class}");
            }

            if (!\is_subclass_of($class, '\RPQ\Server\AbstractJob')) {
                throw new Exception('Job does not implement RPQ\Server\AbstractJob');
            }
            
            $task = new $class(Yii::$app->rpq->getClient(), $job->getId());
            return $task->perform($job->getArgs());
        } catch (Exception $e) {
            // Log the error to the appropriate handler
            Yii::error([
                'message' => 'An error occured when executing the job',
                'jobId' => $job->getId(),
                'workerClass' => $job->getWorkerClass(),
                'queueName' => $this->name,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 'rpq');
            return -1;
        }

        return 0;
    }
}