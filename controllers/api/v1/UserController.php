<?php

namespace app\controllers\api\v1;

use charlesportwoodii\yii2\filters\auth\HMACSignatureAuth;
use charlesportwoodii\yii2\rest\Controller;

use yii\web\HttpException;
use Yii;

class UserController extends Controller
{
    /**
     * Map actions to the appropriate action class
     * @return array
     */
    public function actions()
    {
        return [
            'authenticate'  => 'charlesportwoodii\yii2\api\actions\AuthenticationAction',
            'refresh'       => 'charlesportwoodii\yii2\api\actions\RefreshAction',
            'otp'           => 'charlesportwoodii\yii2\api\actions\OTPAction',
        ];
    }

    /**
     * Yii2 behaviors
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'     => HMACSignatureAuth::className(),
            'only'      => ['refresh', 'authenticate', 'otp'],
            'optional'  => ['authenticate'],
            'except'    => ['options']
        ];

        return $behaviors;
    }
}
