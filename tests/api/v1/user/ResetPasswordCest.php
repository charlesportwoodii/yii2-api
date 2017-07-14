<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use Base32\Base32;
use OTPHP\TOTP;
use Faker\Factory;
use Yii;

use yrc\api\models\Code;

class ResetPasswordCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/reset_password';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];
    
    public function testAuthenticatedPasswordResetFlowPhase1(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an authenticated user phase 1');
        $oldPassword = $I->register(true);
        $I->sendAuthenticatedRequest($this->uri, 'POST');

        // Init the password request request
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }
    
    public function testAuthenticatedPasswordResetFlowPhase2(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an authenticated user phase 2');
        $oldPassword = $I->register(true);

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        
        $code = new Code;
        $code->hash = hash('sha256', $token . '_reset_token');
        $code->user_id = $I->getUser()->id;
        
        expect('code saves', $code->save())->true();

        $payload = [
            'password' => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];

        $I->wantTo('verify a user can reset their password');
        $I->sendAuthenticatedRequest($this->uri . '?reset_token=' . $token, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }

    public function testAuthenticatedPasswordResetFlowPhase3(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an authenticated user phase 3');
        $oldPassword = $I->register(true);

        // Verify reset token needs to be valid
        $payload = [
            'password'          => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];

        $I->sendAuthenticatedRequest($this->uri . '?reset_token=foo', 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }

    public function testUnauthenticatedPasswordResetFlowPhase1(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an unauthenticated user phase 1');
        $oldPassword = $I->register(true);
        
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email
        ]);

        // Init the password request request
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }

    public function testUnauthenticatedPasswordResetFlowPhase2(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an unauthenticated user phase 2');
        $oldPassword = $I->register(true);

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        $code = new Code;
        $code->hash = hash('sha256', $token . '_reset_token');
        $code->user_id = $I->getUser()->id;

        expect('code saves', $code->save())->true();

        $payload = [
            'password' => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];

        $I->wantTo('verify a user can reset their password');
        $I->sendAuthenticatedRequest($this->uri . '?reset_token=' . $token, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }

    public function testUnauthenticatedPasswordResetFlowPhase3(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an unauthenticated user phase3');
        $oldPassword = $I->register(true);

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        $code = new Code;
        $code->hash = hash('sha256', $token . '_reset_token');
        $code->user_id = $I->getUser()->id;

        $payload = [
            'password' => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];
        
        $I->sendAuthenticatedRequest($this->uri . '?reset_token=foo', 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }

    public function testResetWithOTPPhase1(\ApiTester $I)
    {
        $faker = Factory::create();
        $oldPassword = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();
        $I->wantTo('verify password cannot be reset if OTP is enabled phase1');
        $I->sendAuthenticatedRequest($this->uri, 'POST');

        // Init the password request request
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }

    public function testResetWithOTPPhase2(\ApiTester $I)
    {
        $faker = Factory::create();
        $oldPassword = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();
        $I->wantTo('verify password cannot be reset if OTP is enabled phase2');

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        $code = new Code;
        $code->hash = hash('sha256', $token . '_reset_token');
        $code->user_id = $I->getUser()->id;

        expect('code saves', $code->save())->true();

        $payload = [
            'password'          => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];

        $totp = TOTP::create(
            $I->getUser()->otp_secret,
            30,
            'sha256',
            6
        );

        $totp->setLabel($I->getUser()->username);

        $payload['otp'] = (string)$totp->now();

        $I->sendAuthenticatedRequest($this->uri . '?reset_token=' . $token, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }
    
    public function testResetWithoutOTPPhase1(\ApiTester $I)
    {
        $faker = Factory::create();
        $oldPassword = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();
        $I->wantTo('verify password cannot be reset if OTP is enabled phase 1');
        $I->sendAuthenticatedRequest($this->uri, 'POST');

        // Init the password request request
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }

    public function testResetWithoutOTPPhase2(\ApiTester $I)
    {
        $faker = Factory::create();
        $oldPassword = $I->register(true);
        $I->getUser()->provisionOTP();
        $I->getUser()->enableOTP();
        $I->wantTo('verify password cannot be reset if OTP is enabled');

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        $code = new Code;
        $code->hash = hash('sha256', $token . '_reset_token');
        $code->user_id = $I->getUser()->id;

        expect('code saves', $code->save())->true();

        $payload = [
            'password' => $faker->password(20)
        ];
        $payload['password_verify'] = $payload['password'];

        $I->sendAuthenticatedRequest($this->uri . '?reset_token=' . $token, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }
}
