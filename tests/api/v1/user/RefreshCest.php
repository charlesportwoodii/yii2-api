<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use yrc\api\models\EncryptionKey;
use Yii;

class RefreshCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/refresh';
    protected $blockedVerbs = ['put', 'get', 'patch', 'delete'];
    protected $allowedVerbs = ['post'];

    /**
     * Verifies refresh succeeds with a valid token
     * @param ApiTester $I
     */
    public function testRefreshWithValidToken(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify refresh token renews session token');
        $payload = [
            'refresh_token' => $I->getTokens()['refresh_token']
        ];

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

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

    /**
     * Verifies refresh fails with an invalid token
     * @param ApiTester $I
     */
    public function testRefreshWithInvalidToken(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify refresh token does not renew with invalid tokens');
        $payload = [
            'refresh_token' => $I->getTokens()['access_token']
        ];

        $I->sendAuthenticatedRequest($this->uri, 'POST', $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => false,
        ]);
    }

    /**
     * Verifies authentication is required on this endpoint
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
     * @param ApiTester $I
     */
    public function testRefreshWithEncryptedRequestAndEncryptedResponse(\ApiTester $I)
    {
        // Register a user with token
        $password = $I->register(true, true);

        // Generate an encryption key since we don't have a previous request to initialize it off of
        $key = EncryptionKey::generate();
        
        $boxKp = \Sodium\crypto_box_keypair();
        $publicKey = \base64_encode(\Sodium\crypto_box_publickey($boxKp));
        $nonce = \Sodium\randombytes_buf(\Sodium\CRYPTO_BOX_NONCEBYTES);

        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/json+25519');
        $I->haveHttpHeader('x-pubkey', $publicKey);
        $I->haveHttpHeader('Content-Type', 'application/json+25519');
        $I->haveHttpHeader('x-nonce', \base64_encode($nonce));
        $I->wantTo('Send an encrypted response to authenticate and get an encrypted response back');
        
        $kp = \Sodium\crypto_box_keypair_from_secretkey_and_publickey(
            \Sodium\crypto_box_secretkey($boxKp),
            $key->getBoxPublicKey()
        );

        $payload = [
            'refresh_token' => $I->getTokens()['refresh_token']
        ];

        $I->sendAuthenticatedRequest('/api/v1/user/refresh', 'POST', $payload, $nonce, $kp);

        $I->seeResponseCodeIs(200);

        $pub = $I->grabHttpHeader('x-pubkey');
        $sig = $I->grabHttpHeader('x-signature');
        $signing = $I->grabHttpHeader('x-sigpubkey');
        $nonce = $I->grabHttpHeader('x-nonce');

        $kp = \Sodium\crypto_box_keypair_from_secretkey_and_publickey(
            \Sodium\crypto_box_secretkey($boxKp),
            \base64_decode($pub)
        );

        expect('signature is valid', \Sodium\crypto_sign_verify_detached(
            \base64_decode($sig),
            \base64_decode($I->grabResponse()),
            \base64_decode($signing)
        ))->notEquals(false);

        // Decrypt the response
        $response = \Sodium\crypto_box_open(
            \base64_decode($I->grabResponse()),
            \base64_decode($nonce),
            $kp
        );

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
