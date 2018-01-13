<?php

namespace app\controllers\api\v1;

use yrc\filters\auth\HMACSignatureAuth;
use yrc\rest\Controller;

use yrc\actions\ResetPasswordAction;
use yrc\actions\ActivationAction;
use yrc\actions\AuthenticationAction;
use yrc\actions\ChangeEmailAction;
use yrc\actions\OTPAction;
use yrc\actions\RefreshAction;
use yrc\actions\RegisterAction;
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
            'activate'      => ActivationAction::className(),
            'authenticate'  => AuthenticationAction::className(),
            'change_email'  => ChangeEmailAction::className(),
            'otp'           => OTPAction::className(),
            'refresh'       => RefreshAction::className(),
            'register'      => RegisterAction::className(),
            
            // If you want verifiable tokenized password resets (for authenticated and unauthenticated)
            'reset_password' => ResetPasswordAction::className(),

            // If you want password resets done for authenticated userss only
            'reset_password_authenticated' => [
                'class' => ResetPasswordAction::className(),
                'scenario' => ResetPasswordAction::SCENARIO_AUTHENTICATED
            ]
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
