<?php

namespace app\controllers\api\v1;

use yrc\filters\auth\HMACSignatureAuth;
use yrc\rest\Controller;

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
            'activate'      => 'yrc\api\actions\ActivationAction',
            'authenticate'  => 'yrc\api\actions\AuthenticationAction',
            'register'      => 'yrc\api\actions\RegisterAction',
            'refresh'       => 'yrc\api\actions\RefreshAction',
            //'otp'           => 'yrc\api\actions\OTPAction',
            //'reset_password'=> 'yrc\api\actions\PasswordResetAction',
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
            'optional'  => ['authenticate', 'refresh'],
            'except'    => ['options']
        ];

        return $behaviors;
    }
}
