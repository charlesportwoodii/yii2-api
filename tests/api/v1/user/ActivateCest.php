<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use tests\api\v1\user\RegisterCest;
use Base32\Base32;
use Yii;

class ActivateCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/activate';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    public function testActivateWithInvalidToken(\ApiTester $I)
    {
        $I->wantTo('verify user cannot be activated with an invalid activation code');
        $I->sendPOST($this->uri, [
            'activation_code' => 'foo'
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(400);
        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'error' => [
                'message' => [
                    'activation_code' => 'array'
                ]
            ],
            'status' => 'integer'
        ]);

        $I->seeResponseContainsJson([
            'status' => 400,
            'data' => null,
        ]);
    }

    public function testActivateWithValidToken(\ApiTester $I)
    {
        // Run the registration cest instead of rerunning existing tests
        $cest = new RegisterCest;
        $user = $cest->testRegistration(clone $I);

        $token = Base32::encode(\random_bytes(64));
        Yii::$app->cache->set(hash('sha256', $token . '_activation_token'), [
            'id' => $user->id
        ]);

        $I->wantTo('verify user can be activated with a valid activation code');
        $I->sendPOST($this->uri, [
            'activation_code' => $token
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true,
        ]);
    }
}
