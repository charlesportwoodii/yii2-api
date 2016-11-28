<?php

namespace app\controllers\api\v1;

use yrc\filters\auth\HMACSignatureAuth;
use yrc\rest\Controller;

use yrc\api\actions\ResetPasswordAction;
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
            'change_email'  => 'yrc\api\actions\ChangeEmailAction',
            'otp'           => 'yrc\api\actions\OTPAction',
            'refresh'       => 'yrc\api\actions\RefreshAction',
            'register'      => 'yrc\api\actions\RegisterAction',
            
            // If you want verifiable tokenized password resets (for authenticated and unauthenticated)
            'reset_password' => 'yrc\api\actions\ResetPasswordAction',

            // If you want password resets done for authenticated uses only
            'reset_password_authenticated' => [
                'class' => 'yrc\api\actions\ResetPasswordAction',
                'scenario' => ResetPasswordAction::SCENARIO_AUTHENTICATED
            ],
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
            'only'      => ['refresh', 'authenticate', 'otp', 'reset_password', 'change_email', 'reset_password_authenticated'],
            'optional'  => ['authenticate', 'reset_password']
        ];

        return $behaviors;
    }
}
