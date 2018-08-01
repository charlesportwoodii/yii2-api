<?php

namespace app\controllers\api\v1;

use yrc\rest\Controller;
use yrc\actions\OneTimeKeyAction;
use yii\web\HttpException;
use Yii;

class ServerController extends Controller
{
    /**
     * Map actions to the appropriate action class
     *
     * @return array
     */
    public function actions()
    {
        return [
            'otk' => OneTimeKeyAction::class
        ];
    }
}
