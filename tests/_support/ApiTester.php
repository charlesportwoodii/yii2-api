<?php

/**
 * Inherited Methods
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
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * Helper method to send an authenticated Request
     * @param string $uri
     * @param string $method    HTTP method
     * @param array $payload
     * @return void
     */
    public function sendAuthenticatedRequest($uri, $method, $payload = [])
    {
        $now = new \DateTime();
        $time = $now->format(\DateTime::RFC1123);

        $HMAC = HMAC::generate(
            $uri,
            $this->tokens,
            $method,
            $time,
            $payload
        );

        $this->haveHttpHeader('X-DATE', $time);
        $this->haveHttpHeader('Authorization', 'HMAC ' . $this->tokens['access_token'] . ',' . $HMAC);
        $httpMethod = 'send' . $method;

        if (empty($payload)) {
            $this->$httpMethod($uri);
        } else {
            $this->$httpMethod($uri, $payload);
        }

        return $this;
    }
}
