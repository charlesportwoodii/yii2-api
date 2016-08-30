<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use Base32\Base32;
use Faker\Factory;
use Yii;

class ResetPasswordCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/reset_password';

    public function testAuthenticatedPasswordResetFlow(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an authenticated user');
        $oldPassword = $I->register(true);
        $I->sendAuthenticatedRequest($this->uri, 'POST');

        // Init the password request request
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        Yii::$app->cache->set(hash('sha256', $token . '_reset_token'), [
            'id' => $I->getUser()->id
        ]);

        // Verify reset token needs to be valid
        $I->wantTo('verify a password cannot be reset without a valid reset token');
        $payload = [
            'password' => $faker->password(20),
            'reset_token' => 'foo'
        ];
        $payload['password_verify'] = $payload['password'];

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);

        $I->wantTo('verify a user can reset their password');
        $payload['reset_token'] = $token;
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);

        expect('old password does not validate', $I->getUser()->validatePassword($oldPassword))->false();
        expect('new password validates', $I->getUser()->validatePassword($payload['password']))->true();
    }

    public function testUnauthenticatedPasswordResetFlow(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('reset a password as an unauthenticated user');
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

        // Generate a random token since we can't pull this directly from Redis
        $token = Base32::encode(\random_bytes(64));
        Yii::$app->cache->set(hash('sha256', $token . '_reset_token'), [
            'id' => $I->getUser()->id
        ]);

        // Verify reset token needs to be valid
        $I->wantTo('verify a password cannot be reset without a valid reset token');
        $payload = [
            'password' => $faker->password(20),
            'reset_token' => 'foo'
        ];
        $payload['password_verify'] = $payload['password'];

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);

        $I->wantTo('verify a user can reset their password');
        $payload['reset_token'] = $token;
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);

        expect('old password does not validate', $I->getUser()->validatePassword($oldPassword))->false();
        expect('new password validates', $I->getUser()->validatePassword($payload['password']))->true();
    }
}
