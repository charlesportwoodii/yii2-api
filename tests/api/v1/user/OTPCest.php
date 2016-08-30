<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use OTPHP\TOTP;

use Yii;

class OTPCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/otp';
    protected $blockedVerbs = ['put', 'get', 'patch'];
    protected $allowedVerbs = ['post', 'delete'];

    public function testAuthenticationIsRequired(\ApiTester $I)
    {
        foreach ($this->allowedVerbs as $verb) {
            $I->wantTo('verify authentication is required');
            $method = 'send' . $verb;
            $I->$method($this->uri);
            $I->seeResponseIsJson();
            $I->seeResponseCodeIs(401);
        }
    }

    public function testEnablingOTP(\ApiTester $I)
    {
        $I->wantTo('provision and enable OTP');
        $I->register(true);

        // Retrieve a provisioning URL
        $I->sendAuthenticatedRequest($this->uri, 'POST');
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'data' => [
                'provisioning_code' => 'string',
            ],
            'status' => 'integer',
        ]);

        
        $code = \parse_url(\json_decode($I->grabResponse(), true)['data']['provisioning_code']);
        $username = ltrim($code['path'], '/');
        $options = [];
        \parse_str($code['query'], $options);

        // Convert the the otp string into an otp object
        $totp = new TOTP(
            $username,
            $options['secret'],
            30,             // 30 second window
            $options['algorithm'],
            6
        );

        $payload = [
            'code' => '7777'
        ];

        $I->wantTo('verify OTP cannot be enabled with a bad provisioning code');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => false,
        ]);

        $payload = [
            'code' => $totp->now()
        ];

        $I->wantTo('enable OTP with provisioning code');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true,
        ]);
    }

    public function testEnablingOTPWhenEnabled(\ApiTester $I)
    {
        $I->wantTo('verify OTP cannot be enabled if it already is enabled');
        $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();

        $I->sendAuthenticatedRequest($this->uri, 'POST');
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null,
        ]);
    }

    public function testDisablingOTP(\ApiTester $I)
    {
        $I->wantTo('disable OTP');
        $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();

        // Convert the the otp string into an otp object
        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,
            'sha256',
            6
        );

        $payload = [
            'code' => $totp->now()
        ];

        $I->sendAuthenticatedRequest($this->uri, 'DELETE', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true,
        ]);
    }

    public function testDisablingOTPWhenNotEnabled(\ApiTester $I)
    {
        $I->wantTo('verify OTP cannot be disable when it is not enabled');
        $I->register(true);

        
        $I->sendAuthenticatedRequest($this->uri, 'DELETE');

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null,
        ]);
    }

    public function testDisablingOTPWithBadCode(\ApiTester $I)
    {
        $I->wantTo('verify OTP cannot be disabled with a bad code');
        $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();
        $payload = [
            'code' => 'foo'
        ];

        $I->sendAuthenticatedRequest($this->uri, 'DELETE', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer',
        ]);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => false,
        ]);
    }
}