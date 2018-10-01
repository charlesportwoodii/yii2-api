<?php

namespace tests\api\v1\user;

use ncryptf\Request;
use ncryptf\Response;
use tests\_support\AbstractApiCest;
use yrc\models\redis\EncryptionKey;
use Yii;

class RefreshCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/refresh';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    /**
     * Verifies refresh succeeds with a valid token
     *
     * @param ApiTester $I
     */
    public function testRefreshWithValidToken(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify refresh token renews session token');
        $payload = [
            'refresh_token' => $I->getTokens()['refresh_token']
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType(
            [
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
                'ikm' => 'string',
                'expires_at' => 'integer'
            ],
            'status' => 'integer'
            ]
        );
    }

    /**
     * Verifies refresh fails with an invalid token
     *
     * @param ApiTester $I
     */
    public function testRefreshWithInvalidToken(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify refresh token does not renew with invalid tokens');
        $payload = [
            'refresh_token' => $I->getTokens()['access_token']
        ];

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
    }

    /**
     * Verifies authentication is required on this endpoint
     *
     * @param ApiTester $I
     */
    public function testAuthenticationIsRequired(\ApiTester $I)
    {
        $I->wantTo('verify authentication is required');
        $I->sendPOST($this->uri);
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
    }

    /**
     * Verifies that an encrypted refresh can be made
     *
     * @param ApiTester $I
     */
    public function testRefreshWithEncryptedRequestAndEncryptedResponse(\ApiTester $I)
    {
        // Register a user with token
        $user = $I->register(true, true);

        // Generate an encryption key since we don't have a previous request to initialize it off of
        $key = EncryptionKey::generate();
        
        $boxKp = sodium_crypto_box_keypair();
        $publicKey = \base64_encode(sodium_crypto_box_publickey($boxKp));

        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/vnd.25519+json');
        $I->haveHttpHeader('x-pubkey', $publicKey);
        $I->haveHttpHeader('Content-Type', 'application/vnd.25519+json');
        $I->wantTo('Send an encrypted response to authenticate and get an encrypted response back');
        
        $request = new Request(
            sodium_crypto_box_secretkey($boxKp),
            \base64_decode($I->getTokens()->secret_sign_kp)
        );

        $payload = [
            'refresh_token' => $I->getTokens()['refresh_token']
        ];

        $I->sendAuthenticatedRequest('/api/v1/user/refresh', 'POST', $payload, $request, $key->getBoxPublicKey(), 1);

        $I->seeResponseCodeIs(200);

        $pub = $I->grabHttpHeader('x-pubkey');
        $sig = $I->grabHttpHeader('x-signature');
        $signing = $I->grabHttpHeader('x-sigpubkey');
        $nonce = $I->grabHttpHeader('x-nonce');

        $r = new Response(
            sodium_crypto_box_secretkey($boxKp)
        );

        $response = $r->decrypt(
            \base64_decode($I->grabResponse()),
            \base64_decode($pub),
            \base64_decode($nonce)
        );

        expect(
            'signature is valid',
            $r->isSignatureValid(
                $response,
                \base64_decode($sig),
                \base64_decode($signing)
            )
        )->notEquals(false);

        expect('response is not false', $response)->notEquals(false);
        $response = \json_decode($response, true);
        expect('response can be converted into json', \is_array($response))->true();

        expect('response has key [data]', $response)->hasKey('data');
        expect('response has key [status]', $response)->hasKey('status');
        expect('response has key [data][access_token]', $response['data'])->hasKey('access_token');
        expect('response has key [data][refresh_token]', $response['data'])->hasKey('refresh_token');
        expect('response has key [data][ikm]', $response['data'])->hasKey('ikm');
        expect('response has key [data][expires_at]', $response['data'])->hasKey('expires_at');
        expect('response has key [data][signing]', $response['data'])->hasKey('signing');
    }
}
