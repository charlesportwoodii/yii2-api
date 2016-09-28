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
use app\tests\_support\app\HMAC;

use app\forms\Registration;
use app\models\Token;
use Faker\Factory;

class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * Instance of user to reduce lookups
     * @var User
     */
    protected $user;

    /**
     * The tokens
     * @var array
     */
    protected $tokens = [];

    /**
     * Retrieves the user
     * @return User
     */
    public function getUser()
    {
        $this->user->refresh();
        return $this->user;
    }

    /**
     * Retrieves the token
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Sets tokens
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
                'ikm'            => $tokens['ikm']
            ];
        }
    }

    /**
     * Register a new user for testing
     * @return bool
     */
    public function register($activate = true)
    {
        $faker = Factory::create();
        $form = new Registration;
        $form->email = $faker->safeEmail;
        $form->username = $faker->username(10);
        $form->password = $faker->password(20);
        $form->password_verify = $form->password;

        expect('form registers', $form->register())->true();
        $this->user = Yii::$app->yrc->userClass::findOne(['email' => $form->email]);

        if ($activate === true) {
            $this->user->activate();
        }

        expect('user is not null', $this->user !== null)->true();
        $this->tokens = Token::generate($this->user->id);
        $this->addTokens($this->tokens);
        
        return $form->password;
    }

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

        $tokens = $this->getTokens();
        $HMAC = HMAC::generate(
            $uri,
            $tokens,
            $method,
            $time,
            $payload
        );

        $this->haveHttpHeader('X-DATE', $time);
        $this->haveHttpHeader('Authorization', 'HMAC ' . $tokens['access_token'] . ',' . $HMAC);
        $httpMethod = 'send' . $method;

        if (empty($payload)) {
            $this->$httpMethod($uri);
        } else {
            $this->$httpMethod($uri, $payload);
        }

        return $this;
    }
}

