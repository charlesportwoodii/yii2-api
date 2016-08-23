<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;

/**
 * Tests API authentication
 * @class AuthenticationCest
 */
class AuthenticateCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/authenticate';
    protected $blockedVerbs = ['put', 'get', 'patch'];
    protected $allowedVerbs = ['post', 'delete'];

    /**
     * Tests logging into the API
     * @param ApiTester $I
     */
    public function testLoginWithValidCredentials(\ApiTester $I)
    {
        $password = $this->register(true);
        $I->wantTo('verify users can authenticate against the API');
        $I->sendPOST($this->uri, [
            'email' => $this->user->email,
            'password' => $password
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
    }

    /**
     * Tests logging into the API
     * @param ApiTester $I
     */
    public function testLoginWithInvalidCredentials(\ApiTester $I)
    {

    }

    public function testLoginWithOTP(\ApiTester $I)
    {

    }

    public function testDeuathenticate(\ApiTester $I)
    {

    }

    public function testDeauthenticateAllSessions(\ApiTester $I)
    {

    }
}
