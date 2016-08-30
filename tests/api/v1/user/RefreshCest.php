<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use tests\api\v1\user\AuthenticateCest;
use Yii;

class RefreshCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/refresh';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    public function testRefreshWithValidToken(\ApiTester $I)
    {
        // Tests refreshing tokens using existing authentication method
        $cest = new AuthenticateCest;
        $data = $cest->testLoginWithValidCredentials(clone $I);
        $I->wantTo('verify refresh token renews session token');
        $payload = [
            'refresh_token' => $data['refresh_token']
        ];
        
        $I->addTokens($data);

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeHttpHeaderOnce('X-Rate-Limit-Limit');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Remaining');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Reset');

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

        $newTokens = \json_decode($I->grabResponse(), true)['data'];
        
        // Verify the tokens are wiped and that existing tokens cannot be reused
        $I->wantTo('verify refresh token is wiped');
        $payload = [
            'refresh_token' => $data['refresh_token']
        ];
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);

        // Verify that the new tokens do work on an authenticated request
        $I->wantTo('verify new refresh tokens work');
        $payload = [
            'refresh_token' => $newTokens['refresh_token']
        ];

        $I->addTokens($newTokens);
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeHttpHeaderOnce('X-Rate-Limit-Limit');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Remaining');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Reset');

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

    
    public function testRefreshWithInvalidToken(\ApiTester $I)
    {
        // Tests refreshing tokens using existing authentication method
        $cest = new AuthenticateCest;
        $data = $cest->testLoginWithValidCredentials(clone $I);
        $I->wantTo('verify refresh token does not renew with invalid tokens');
        $payload = [
            'refresh_token' => $data['access_token']
        ];
        
        $I->addTokens($data);

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeHttpHeaderOnce('X-Rate-Limit-Limit');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Remaining');
        $I->seeHttpHeaderOnce('X-Rate-Limit-Reset');

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => false,
        ]);
    }

    public function testRefreshRequiresAuthentication(\ApiTester $I)
    {
        // Tests refreshing tokens using existing authentication method
        $cest = new AuthenticateCest;
        $data = $cest->testLoginWithValidCredentials(clone $I);
        $I->wantTo('verify refresh token does not renew with invalid tokens');
        $payload = [
            'refresh_token' => $data['refresh_token']
        ];

        $data['access_token'] = 'foo';
        
        $I->addTokens($data);
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);

        $I->seeResponseContainsJson([
            'status' => 401,
            'data' => null,
        ]);
    }
}