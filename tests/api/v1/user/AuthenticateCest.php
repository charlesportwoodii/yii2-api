<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use yrc\api\models\TokenKeyPair;
use yii\helpers\Json;
use OTPHP\TOTP;
use Faker\Factory;

/**
 * Tests API authentication
 * @class AuthenticationCest
 */
class AuthenticateCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/authenticate';
    protected $blockedVerbs = ['put', 'get', 'patch'];
    protected $allowedVerbs = ['post', 'delete'];

    /**
     * Tests logging into the API
     * @param ApiTester $I
     */
    public function testLoginWithValidCredentials(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API');
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password
        ]);

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

        return \json_decode($I->grabResponse(), true)['data'];
    }

    /**
     * Tests logging into the API
     * @param ApiTester $I
     */
    public function testLoginWithInvalidCredentials(\ApiTester $I)
    {
        $I->register(true);
        $faker = Factory::create();

        $I->wantTo('verify authentication API endpoint work');
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $faker->password(20)
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
        ]);
    }

    /**
     * Tests an authenticated request to the API to deauthenticate the current request
     * @param ApiTester $I
     */
    public function testDeauthenticate(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify users can de-authenticate via HMAC authentication');
        $I->sendAuthenticatedRequest($this->uri, 'DELETE');
        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'status' => 200,
            'data' => true
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'boolean',
            'status' => 'integer'
        ]);
    }

    /**
     * Tests logging into the API with OTP enabled
     * @param ApiTester $I
     */
    public function testLoginWithOTP(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API with 2FA enabled');
        expect('OTP is provisioned', $I->getUser()->provisionOTP())->notEquals(false);
        expect('OTP is enabled', $I->getUser()->enableOTP())->true();

        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );

        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password,
            'otp' => $totp->now()
        ]);

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
     * Tests logging into the API with OTP enabled
     * @param ApiTester $I
     */
    public function testLoginWithBadOTP(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify a valid TOTP 2FA code is required');
        expect('OTP is provisioned', $I->getUser()->provisionOTP())->notEquals(false);
        expect('OTP is enabled', $I->getUser()->enableOTP())->true();

        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );

        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password,
            'otp' => $totp->at(100)
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
        ]);
    }

    /**
     * Tests logging into the API with OTP enabled, but not provided
     * @param ApiTester $I
     */
    public function testLoginWithoutOTP(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify if 2FA is enabled the correct status code is returned');
        expect('OTP is provisioned', $I->getUser()->provisionOTP())->notEquals(false);
        expect('OTP is enabled', $I->getUser()->enableOTP())->true();

        $totp = new TOTP(
            $I->getUser()->username,
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );

        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password
        ]);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson([
            'status' => 401
        ]);

        $I->seeResponseMatchesJsonType([
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
        ]);

        $I->seeResponseContainsJson([
            'status' => 401,
            'data' => null,
            'error' => [
                'code' => 1
            ]
        ]);
    }

    /**
     * Tests logging into the API with an encrypted payload
     * @param ApiTester $I
     */
    public function testLoginWithEncryptedPayload(\ApiTester $I)
    {
        $password = $I->register(true);
        $I->wantTo('verify users can authenticate against the API with an encrypted payload');
        
        // Simulate a request to /api/v1/server/otk
        $token = TokenKeyPair::generate(TokenKeyPair::OTK_TYPE);
        
        // Set the content type to application/json+25519
        $I->haveHttpHeader('content-type', 'application/json+25519');
        
        // Also set the hash ID so the API knows what hash to fetch
        $I->haveHttpHeader('x-hashid', $token->hash);

        $boxKp = \Sodium\crypto_box_keypair();
        $publicKey = \base64_encode(\Sodium\crypto_box_publickey($boxKp));

        // The payload is now encrypted
        $payload = \base64_encode(\Sodium\crypto_box_seal(
            \json_encode([
                'email' => $I->getUser()->email,
                'password' => $password,
                'pubkey' => $publicKey,
            ]),
            $token->getBoxPublicKey()
        ));

        $I->sendPOST($this->uri, $payload);

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(200);
        $I->seeResponseMatchesJsonType([
            'data' => [
                'access_token' => 'string',
                'refresh_token' => 'string',
                'ikm' => 'string',
                'crypt' => [
                    'public' => 'string',
                    'signing' => 'string',
                    'signature' => 'string',
                    'hash' => 'string'
                ],
                'expires_at' => 'integer'
            ],
            'status' => 'integer'
        ]);
    }

    /**
     * Tests logging into the API with an encrypted payload
     * @param ApiTester $I
     */
    public function testLoginWithEncryptedPayloadAndEncryptedResponse(\ApiTester $I)
    {
        $password = $I->register(true, false);
        $I->wantTo('verify users can authenticate against the API with an encrypted payload and that the response payload is encrypted');
        
        // Simulate a request to /api/v1/server/otk
        $token = TokenKeyPair::generate(TokenKeyPair::OTK_TYPE);
        
        // Set the content type to application/json+25519
        $I->haveHttpHeader('content-type', 'application/json+25519');
        $I->haveHttpHeader('accept', 'application/json+25519');

        $boxKp = \Sodium\crypto_box_keypair();
        $publicKey = \base64_encode(\Sodium\crypto_box_publickey($boxKp));

        // Also set the hash ID so the API knows what hash to fetch
        $I->haveHttpHeader('x-hashid', $token->hash);

        // The payload is now encrypted
        $payload = \base64_encode(\Sodium\crypto_box_seal(
            \json_encode([
                'email'         => $I->getUser()->email,
                'password'      => $password,
                'pubkey'        => $publicKey
            ]),
            $token->getBoxPublicKey()
        ));

        $I->sendPOST($this->uri, $payload);

        $I->seeHttpHeader('x-nonce');
        $I->seeHttpHeader('x-pubkey');
        $serverPubKey = $I->grabHttpHeader('x-pubkey');
        $nonce = $I->grabHttpHeader('x-nonce');

        $kp = \Sodium\crypto_box_keypair_from_secretkey_and_publickey(
            \Sodium\crypto_box_secretkey($boxKp),
            \base64_decode($serverPubKey)
        );

        // Decrypt the response
        $response = \Sodium\crypto_box_open(
            \base64_decode($I->grabResponse()),
            \base64_decode($nonce),
            $kp
        );

        $I->seeResponseCodeIs(200);

        expect('response is not false', $response)->notEquals(false);
        $response = \json_decode($response, true);
        expect('response can be converted into json', \is_array($response))->true();

        expect('response has key [data]', $response)->hasKey('data');
        expect('response has key [status]', $response)->hasKey('status');
        expect('response has key [data][access_token]', $response['data'])->hasKey('access_token');
        expect('response has key [data][refresh_token]', $response['data'])->hasKey('refresh_token');
        expect('response has key [data][ikm]', $response['data'])->hasKey('ikm');
        expect('response has key [data][expires_at]', $response['data'])->hasKey('expires_at');
        expect('response has key [data][crypt]', $response['data'])->hasKey('crypt');
        expect('response has key [data][crypt][public]', $response['data']['crypt'])->hasKey('public');
        expect('response has key [data][crypt][signing]', $response['data']['crypt'])->hasKey('signing');
        expect('response has key [data][crypt][signature]', $response['data']['crypt'])->hasKey('signature');
        expect('response has key [data][crypt][hash]', $response['data']['crypt'])->hasKey('hash');

        return [
            'response'  => $response,
            'boxKp'     => $boxKp,
            'public'    => $publicKey,
            'kp'        => $kp,
            'serverPub' => $serverPubKey
        ];
    }

    /**
     * Tests that requests after authentication can be encrypted
     * @param ApiTester $I
     */
    public function testAuthAndRefreshWithEncryption(\ApiTester $I)
    {
        $I2 = clone $I;
        extract($this->testLoginWithEncryptedPayloadAndEncryptedResponse($I2));

        // Add the new tokens
        $I->addTokens($response['data']);
        $I->wantTo('Verify my credentials can be refreshed');
        $I->haveHttpHeader('content-type', 'application/json+25519');
        $I->haveHttpHeader('accept', 'application/json+25519');

        // Set the hashid that should be used so the server knows what key to pull, and the nonce for this message
        $nonce = \Sodium\randombytes_buf(\Sodium\CRYPTO_BOX_NONCEBYTES);
        $I->haveHttpHeader('x-hashid', $response['data']['crypt']['hash']);
        $I->haveHttpHeader('x-nonce', \base64_encode($nonce));

        $newBoxKp = \Sodium\crypto_box_keypair();
        $newPublicKey = \base64_encode(\Sodium\crypto_box_publickey($newBoxKp));

        // The payload is now encrypted
        $payload = [
            'refresh_token' => $response['data']['refresh_token'],
            'public_key' => $newPublicKey
        ];

        $I->sendAuthenticatedRequest('/api/v1/user/refresh', 'POST', $payload, $nonce, $kp);

        $I->seeResponseCodeIs(200);

        $I->seeHttpHeader('x-nonce');
        $I->seeHttpHeader('x-pubkey');
        $I->seeHttpHeader('x-signature');
        $I->seeHttpHeader('x-sigpubkey');
        $serverPubKey = $I->grabHttpHeader('x-pubkey');
        $nonce = $I->grabHttpHeader('x-nonce');
        $signature = $I->grabHttpHeader('x-signature');
        $sigPublicKey = $I->grabHttpHeader('x-sigpubkey');

        $newKp = \Sodium\crypto_box_keypair_from_secretkey_and_publickey(
            \Sodium\crypto_box_secretkey($newBoxKp),
            \base64_decode($serverPubKey)
        );

        expect('signature is valid', \Sodium\crypto_sign_verify_detached(
            \base64_decode($signature),
            \base64_decode($I->grabResponse()),
            \base64_decode($sigPublicKey)
        ))->notEquals(false);

        $response = \Sodium\crypto_box_open(
            \base64_decode($I->grabResponse()),
            \base64_decode($nonce),
            $newKp
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
        expect('response has key [data][crypt]', $response['data'])->hasKey('crypt');
        expect('response has key [data][crypt][public]', $response['data']['crypt'])->hasKey('public');
        expect('response has key [data][crypt][signing]', $response['data']['crypt'])->hasKey('signing');
        expect('response has key [data][crypt][signature]', $response['data']['crypt'])->hasKey('signature');
        expect('response has key [data][crypt][hash]', $response['data']['crypt'])->hasKey('hash');

        // Verify the new signing public key in the header matches what is in the response
        expect('new signing key equals signing key in header', $response['data']['crypt']['signing'])->equals($sigPublicKey);
    }
}
