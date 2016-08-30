<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use yii\helpers\Json;
use OTPHP\TOTP;
use Faker\Factory;

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
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API');
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
                'ikm' => 'string',
                'expires_at' => 'integer'
            ],
            'status' => 'integer'
        ]);

        return \json_decode($I->grabResponse(), true)['data'];
    }

    /**
     * Tests logging into the API
     * @param ApiTester $I
     */
    public function testLoginWithInvalidCredentials(\ApiTester $I)
    {
        $I->register(true);
        $faker = Factory::create();

        $I->wantTo('verify authentication API endpoint work');
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $faker->password(20)
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
        ]);
    }

    /**
     * Tests an authenticated request to the API to deauthenticate the current request
     * @param ApiTester $I
     */
    public function testDeauthenticate(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify users can de-authenticate via HMAC authentication');
        $I->sendAuthenticatedRequest($this->uri, 'DELETE');
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer'
        ]);

        $I->seeHttpHeaderOnce('X-Rate-Limit-Limit');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Remaining');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Reset');
    }

    /**
     * Tests logging into the API with OTP enabled
     * @param ApiTester $I
     */
    public function testLoginWithOTP(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API with 2FA enabled');
        expect('OTP is provisioned', $I->getUser()->provisionOTP())->notEquals(false);
        expect('OTP is enabled', $I->getUser()->enableOTP())->true();

        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );

        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password,
            'otp' => $totp->now()
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
                'ikm' => 'string',
                'expires_at' => 'integer'
            ],
            'status' => 'integer'
        ]);
    }

    /**
     * Tests logging into the API with OTP enabled
     * @param ApiTester $I
     */
    public function testLoginWithBadOTP(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API with 2FA enabled');
        expect('OTP is provisioned', $I->getUser()->provisionOTP())->notEquals(false);
        expect('OTP is enabled', $I->getUser()->enableOTP())->true();

        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );

        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password,
            'otp' => $totp->at(100)
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
        ]);
    }
}
