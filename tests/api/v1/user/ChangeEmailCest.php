<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use Faker\Factory;
use Yii;

class ChangeEmailCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/change_email';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    public function testAuthenticationIsRequired(\ApiTester $I)
    {
        $I->wantTo('verify POST requires authentication');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST($this->uri);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401,
            'data' => null
        ]);
    }

    public function testWithoutEmail(\ApiTester $I)
    {
        $I->wantTo('verify an email is required');
        $oldPassword = $I->register(true);
        $payload = [
            'password' => $I->getPassword()
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }

    public function testWithoutPassword(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('verify the users current password is required');
        $user = $I->register(true);
        $payload = [
            'email' => $faker->safeEmail
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }

    public function testWithInvalidPassword(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('verify the users current password is required');
        $oldPassword = $I->register(true);
        $payload = [
            'email' => $faker->safeEmail,
            'password' => 'random password not valid'
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null
        ]);
    }

    public function testEmailCanBeChanged(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('verify the email address can be changed');
        $user = $I->register(true);
        $payload = [
            'email' => $faker->safeEmail,
            'password' => $I->getPassword()
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);
    }
}
