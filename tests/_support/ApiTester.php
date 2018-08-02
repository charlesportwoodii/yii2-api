<?php

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
use tests\_support\HMAC;

use common\forms\Registration;
use common\models\Token;
use Faker\Factory;

class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;
    use \tests\_support\traits\UserTrait;

    /**
     * Instance of user to reduce lookups
     *
     * @var User
     */
    protected $user;

    /**
     * The tokens
     *
     * @var array
     */
    protected $tokens = [];

    /**
     * Retrieves the user
     *
     * @return User
     */
    public function getUser()
    {
        $this->user->refresh();
        return $this->user;
    }

    /**
     * Retrieves the token
     *
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Sets tokens
     *
     * @param array $tokens
     */
    public function addTokens($tokens)
    {
        if (isset($tokens['access_token'])) {
            $this->tokens = $tokens;
        } else {
            $this->tokens = [
                'access_token'   => $tokens['access_token'],
                'refresh_token'  => $tokens['refresh_token'],
                'ikm'            => $tokens['ikm'],
            ];
        }
    }

    /**
     * Helper method to send an authenticated Request
     *
     * @param  string $uri
     * @param  string $method  HTTP method
     * @param  array  $payload
     * @param  array  $nonce
     * @param  array  $kp
     * @return void
     */
    public function sendAuthenticatedRequest($uri, $method, $payload = [], $nonce = null, $kp = null)
    {
        $now = new \DateTime();
        $time = $now->format(\DateTime::RFC1123);

        $tokens = $this->getTokens();
        $HMAC = HMAC::generate(
            $uri,
            $tokens,
            $method,
            $time,
            $payload
        );

        $this->haveHttpHeader('Authorization', 'HMAC ' . \base64_encode(json_encode([
            'hmac' => $HMAC['hmac'],
            'v' => $HMAC['v'],
            'access_token' => $tokens['access_token'],
            'salt' => $HMAC['salt'],
            'date' => $HMAC['date']
        ])));
        $httpMethod = 'send' . $method;

        if (empty($payload)) {
            $this->$httpMethod($uri);
        } else {
            if ($nonce !== null && $kp !== null) {
                // The payload is now encrypted
                $payload = \base64_encode(
                    sodium_crypto_box(
                        \json_encode($payload),
                        $nonce,
                        $kp
                    )
                );
            }
            $this->$httpMethod($uri, $payload);
        }

        return $this;
    }
}
