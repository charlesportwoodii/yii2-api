<?php

namespace api\actions;

use yrc\rest\Action;
use yrc\web\Response;
use yii\helpers\Json;
use Yii;
use DateTime;

/**
 * @class PostmanAuthAction
 * Helper to assist with generation the necessary auth headers for postman
 */
class PostmanAuthAction extends Action
{
    /**
     * @param array $params
     */
    public function post(array $params = [])
    {
        $response = new Response;
        $response->format = Response::FORMAT_JSON;
        
        $now = new \DateTime();
        $time = $now->format(\DateTime::RFC1123);

        $requestUrl = Yii::$app->request->post('url');
        $uri = '/' . implode('/', $requestUrl['path']);
        $queryParams = [];
        foreach ($requestUrl['query'] as $param) {
            $queryParams[$param['key']] = $param['value'];
        }
        $query = \http_build_query($queryParams);

        require __DIR__ . '/../../tests/_support/HMAC.php';

        $payload = Yii::$app->request->post('payload');
        $url = $uri;
        if ($query != '') {
            $url .= '?' . $query;
        }

        $hmac = \tests\_support\HMAC::generate(
            $url,
            Yii::$app->request->post('tokens'),
            Yii::$app->request->post('method'),
            $time,
            $payload,
            true
        );

        $response->data = explode(',', $hmac);
        $response->data[] = $time;
        return $response;
    }
}
