<?php

namespace tests\_support;

use ncryptf\Authorization;
use ncryptf\Token;
use yii\helpers\Json;
use Yii;

/**
 * Helper class to generate a HMAC signature
 */
final class HMAC
{
    /**
     * Static method to generate the HMAC
     *
     * @param  string  $uri
     * @param  array   $tokens
     * @param  string  $method
     * @param  string  $date
     * @param  array   $payload
     * @param  boolean $payloadIsJson
     * @return string
     */
    public static function generate($uri, $tokens, $method, $date, $payload = '', $payloadIsJson = false)
    {
        $token = new Token(
            $tokens['access_token'],
            $tokens['refresh_token'],
            \base64_decode($tokens['ikm']),
            \base64_decode($tokens['secret_sign_kp']) ?? '',
            $tokens['expires_at'] ?? \time() + (60*60*60)
        );

        if (!$payloadIsJson && $payload !== '') {
            $payload = Json::encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
        }

        $auth = new Authorization($method, $uri, $token, $date, $payload);

        Yii::info([
            'signature' => $auth->getSignatureString()
        ]);

        return $auth->getHeader();
    }
}
