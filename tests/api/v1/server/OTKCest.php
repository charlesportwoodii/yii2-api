<?php

namespace tests\api\v1\server;

use tests\_support\AbstractApiCest;
use Base32\Base32;
use Yii;

class OtkCest extends AbstractApiCest
{
    protected $uri = '/api/v1/server/otk';
    protected $blockedVerbs = ['put', 'post', 'patch', 'delete'];
    protected $allowedVerbs = ['get'];

    /**
     * Test that OTK tokens can be generated and verify that they are valid
     */
    public function testGetOTK(\ApiTester $I)
    {
        $I->wantTo('get otk tokens');
        $I->sendGET($this->uri);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseMatchesJsonType([
            'status' => 'integer',
            'data' => [
                'public' => 'string',
                'signing' => 'string',
                'signature' => 'string',
                'expires_at' => 'integer',
                'hash' => 'string'
            ]
        ]);

        $response = \json_decode($I->grabResponse(), true)['data'];

        $public = $response['public'];
        $signing = $response['signing'];
        $signature = $response['signature'];

        // Verify the signature is accurate
        expect('signature is valid', \Sodium\crypto_sign_verify_detached(
            \base64_decode($signature),
            \base64_decode($public),
            \base64_decode($signing)
        ))->notEquals(false);
    }
}