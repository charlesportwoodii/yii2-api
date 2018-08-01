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

        $I->seeResponseMatchesJsonType(
            [
            'status' => 'integer',
            'data' => [
                'public' => 'string',
                'hash' => 'string'
            ]
            ]
        );
    }
}
