<?php

namespace api\controllers\v1;

use api\actions\VersionAction;
use api\actions\AppleAppSiteAssociationAction;
use api\actions\PostmanAuthAction;
use yrc\rest\Controller;
use yrc\web\Response;
use yii\web\HttpException;
use Yii;

class InfoController extends Controller
{
    public function actions()
    {
        $actions = [
            'version' => VersionAction::class,
        ];

        if (YII_DEBUG) {
            $actions['postman-auth'] = PostmanAuthAction::class;
        }
        return $actions;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        return $behaviors;
    }
}
