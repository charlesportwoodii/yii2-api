<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use Base32\Base32;
use OTPHP\TOTP;
use Faker\Factory;
use Yii;

use yrc\models\redis\Code;

class ResetPasswordAuthenticatedCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/reset_password_authenticated';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    public function testAuthenticatedPasswordResetFlow(\ApiTester $I)
    {
        $I->wantTo('reset a password as an authenticated user');
        $faker = Factory::create();
        $user = $I->register(true);

        $payload = [
            'password' => $faker->password(20),
            'old_password' => $I->getPassword()
        ];
        $payload['password_verify'] = $payload['password'];

        $I->wantTo('verify a user can reset their password');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            [
            'status' => 200,
            'data' => true
            ]
        );
    }

    public function testAuthenticatedPasswordResetFlowWithOTPEnabled(\ApiTester $I)
    {
        $I->wantTo('reset a password as an authenticated user');
        $faker = Factory::create();
        $user = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();

        $payload = [
            'password' => $faker->password(20),
            'old_password' => $I->getPassword()
        ];
        $payload['password_verify'] = $payload['password'];

        $I->wantTo('verify a user can reset their password');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson(
            [
            'status' => 400,
            'data' => null
            ]
        );
    }

    public function testAuthenticatedPasswordResetFlowWithOTPEnabledAndPresent(\ApiTester $I)
    {
        $I->wantTo('reset a password as an authenticated user with otp');
        $faker = Factory::create();
        $user = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();

        $payload = [
            'password' => $faker->password(20),
            'old_password' => $I->getPassword()
        ];
        $payload['password_verify'] = $payload['password'];
        // Verify the request to valid OTP keys
        $totp = TOTP::create(
            $I->getUser()->otp_secret,
            30,
            'sha256',
            6
        );
        $totp->setLabel($I->getUser()->username);

        $payload['otp'] = (string)$totp->now();

        $I->wantTo('verify a user can reset their password');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson(
            [
            'status' => 200,
            'data' => true
            ]
        );
    }
}