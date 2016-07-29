<?php

namespace tests\_support;
use ApiTester;

use app\tests\_support\app\HMAC;

/**
 * Supporter class for API Cests
 * @class ApiCest
 */
class ApiCest
{ 
    /**
     * The URI to implement
     * @var string $uri
     */
    protected $uri = NULL;
    
    /**
     * Token data
     * @var UserToken $_tokens
     */
    protected $tokens;
    
    /**
     * Instance of user to reduce lookups
     * @var User
     */
    protected $user;
    
    /**
     * Before test
     * @param ApiTester
     */
    public function _before(ApiTester $I)
    {
        User::deleteAll();
        //RegistrationToken::deleteAll();
        expect('uri is set', $this->uri)->notEquals(null);
    }
    
    /**
     * Register a new user
     * @todo: this should really be a fixture somehow...
     * @return bool
     */
    protected function register($username = 'example', $password = 'example1234')
    {
        //$form = new UserForm(['scenario' => 'register']);
        $form->username = $username;
        $form->password = $password;
        //$form->registrationToken = RegistrationToken::generate($form->username);

        expect('registration token is set', $form->registrationToken)->notEquals(false);
        expect('form registers', $form->register())->true();
        $this->user = $form->getUser();
        
        $this->tokens = UserToken::generate(User::findOne(['username' => 'example'])->id);
        
        return true;
    }

    /**
     * Helper method to add tghe necessary headers to the request
     * @param ApiTester $I
     * @param string $uri
     * @param string $method    HTTP method
     * @param array $payload
     * @return void
     */
    protected function addHeaders(&$I, $uri, $method, $payload = [])
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

        $I->haveHttpHeader('X-DATE', $time);
        $I->haveHttpHeader('Authorization', 'HMAC '.$this->tokens['access_token'].','.$HMAC);
    }
}
