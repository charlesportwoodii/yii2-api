<?php

namespace tests\api\v1\user;

use tests\_support\AbstractApiCest;
use yrc\api\models\EncryptionKey;
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

        $totp = TOTP::create(
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );
        $totp->setLabel($I->getUser()->username);

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

        $totp = TOTP::create(
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );
        $totp->setLabel($I->getUser()->username);

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

        $totp = TOTP::create(
            $I->getUser()->otp_secret,
            30,             // 30 second window
            'sha256',       // SHA256 for the hashing algorithm
            6               // 6 digits
        );
        $totp->setLabel($I->getUser()->username);

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
     * Sends a plain text request, and expects that the response is encrypted using our newly generated public key
     * @param ApiTester
     */
    public function testAuthenticatePlainTextToEncryptedResponse(\ApiTester $I)
    {
        // Create a new user
        $password = $I->register(true);

        // Generate a new encryption key, mocking a request to /api/v1/server/otk
        $key = EncryptionKey::generate();
        
        $boxKp = sodium_crypto_box_keypair();
        $publicKey = \base64_encode(sodium_crypto_box_publickey($boxKp));
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        
        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/json+25519');
        $I->haveHttpHeader('x-pubkey', $publicKey);
        $I->haveHttpHeader('x-nonce', \base64_encode($nonce));

        $I->wantTo('Send an plain text response to authenticate and get an encrypted repsonse back');
        // The payload is now encrypted
        $I->sendPOST($this->uri, [
            'email' => $I->getUser()->email,
            'password' => $password,
        ]);

        // We should get an encrypted HTTP 200 response back
        $I->seeResponseCodeIs(200);

        $pub = $I->grabHttpHeader('x-pubkey');
        $sig = $I->grabHttpHeader('x-signature');
        $signing = $I->grabHttpHeader('x-sigpubkey');
        $nonce = $I->grabHttpHeader('x-nonce');

        $kp = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_crypto_box_secretkey($boxKp),
            \base64_decode($pub)
        );

        expect('signature is valid', sodium_crypto_sign_verify_detached(
            \base64_decode($sig),
            \base64_decode($I->grabResponse()),
            \base64_decode($signing)
        ))->notEquals(false);
        
        // Decrypt the response
        $response = sodium_crypto_box_open(
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

    /**
     * Encrypts the response before sending it to the API for authenticate, then verifies the response itself is encrypted.
     * @param ApiTester $I
     */
    public function testAuthenticatewithEncryptedRequestAndEncryptedResponse(\ApiTester $I)
    {
        // Create a new user
        $password = $I->register(true);

        // Generate a new encryption key, mocking a request to /api/v1/server/otk
        $key = EncryptionKey::generate();
        
        $boxKp = sodium_crypto_box_keypair();
        $publicKey = \base64_encode(sodium_crypto_box_publickey($boxKp));
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/json+25519');
        $I->haveHttpHeader('x-pubkey', $publicKey);
        $I->haveHttpHeader('Content-Type', 'application/json+25519');
        $I->haveHttpHeader('x-nonce', \base64_encode($nonce));
        $I->wantTo('Send an encrypted response to authenticate and get an encrypted response back');
        
        $kp = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_crypto_box_secretkey($boxKp),
            $key->getBoxPublicKey()
        );

        $payload = \base64_encode(sodium_crypto_box(
            \json_encode([
                'email'         => $I->getUser()->email,
                'password'      => $password
            ]),
            $nonce,
            $kp
        ));

        // Send the encrypted response
        $I->sendPOST($this->uri, $payload);

        // We should get an encrypted HTTP 200 response back
        $I->seeResponseCodeIs(200);

        $pub = $I->grabHttpHeader('x-pubkey');
        $sig = $I->grabHttpHeader('x-signature');
        $signing = $I->grabHttpHeader('x-sigpubkey');
        $nonce = $I->grabHttpHeader('x-nonce');

        $kp = sodium_crypto_box_keypair_from_secretkey_and_publickey(
            sodium_crypto_box_secretkey($boxKp),
            \base64_decode($pub)
        );

        expect('signature is valid', sodium_crypto_sign_verify_detached(
            \base64_decode($sig),
            \base64_decode($I->grabResponse()),
            \base64_decode($signing)
        ))->notEquals(false);
        
        // Decrypt the response
        $response = sodium_crypto_box_open(
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