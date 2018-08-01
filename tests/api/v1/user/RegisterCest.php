<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use Faker\Factory;
use Yii;

class RegisterCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/register';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    public function testRegistrationWithInvalidFormData(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('verify user registration fails');
        $I->sendPOST(
            $this->uri, [
            'email'             => $faker->email,
            'username'          => $faker->username,
            'password'          => $faker->password(24),
            'password_verify'   => $faker->password(24),
            ]
        );

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType(
            [
            'data' => 'null',
            'error' => [
                'message' => [
                    'password_verify' => 'array'
                ]
            ],
            'status' => 'integer'
            ]
        );

        $I->seeResponseContainsJson(
            [
            'status' => 400,
            'data' => null,
            ]
        );
    }

    public function testRegistration(\ApiTester $I)
    {
        $faker = Factory::create();
        $I->wantTo('verify user registration');
        $password = $faker->password(24);
        $payload = [
            'email'             => $faker->email,
            'username'          => $faker->username,
            'password'          => $password,
            'password_verify'   => $password
        ];

        $I->sendPOST($this->uri, $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(
            [
            'data' => 'boolean',
            'status' => 'integer'
            ]
        );

        $I->seeResponseContainsJson(
            [
            'status' => 200,
            'data' => true
            ]
        );
    }
}