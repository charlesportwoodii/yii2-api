<?php

namespace tests\api\v1\user;

use ncryptf\Request;
use ncryptf\Response;
use tests\_support\AbstractApiCest;
use yrc\models\redis\EncryptionKey;
use yii\helpers\Json;
use OTPHP\TOTP;
use Faker\Factory;

/**
 * Tests API authentication
 *
 * @class AuthenticationCest
 */
class AuthenticateCest extends AbstractApiCest
{
    protected $uri = '/api/v1/user/authenticate';
    protected $blockedVerbs = ['put', 'get', 'patch'];
    protected $allowedVerbs = ['post', 'delete'];

    /**
     * Tests logging into the API
     *
     * @param ApiTester $I
     */
    public function testLoginWithValidCredentials(\ApiTester $I)
    {
        $user = $I->register(true);
        $I->wantTo('verify users can authenticate against the API');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $I->getPassword()
            ]
        );

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

        return \json_decode($I->grabResponse(), true)['data'];
    }

    /**
     * Tests logging into the API
     *
     * @param ApiTester $I
     */
    public function testLoginWithInvalidCredentials(\ApiTester $I)
    {
        $I->register(true);
        $faker = Factory::create();

        $I->wantTo('verify authentication API endpoint work');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $faker->password(20)
            ]
        );

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(
            [
            'status' => 401
            ]
        );

        $I->seeResponseMatchesJsonType(
            [
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
            ]
        );
    }

    /**
     * Tests an authenticated request to the API to deauthenticate the current request
     *
     * @param ApiTester $I
     */
    public function testDeauthenticate(\ApiTester $I)
    {
        $I->register(true);
        $I->wantTo('verify users can de-authenticate via HMAC authentication');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
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
     *
     * @param ApiTester $I
     */
    public function testLoginWithOTP(\ApiTester $I)
    {
        $user = $I->register(true);
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

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $I->getPassword(),
                'otp' => $totp->now()
            ]
        );

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
     * Tests logging into the API with OTP enabled
     *
     * @param ApiTester $I
     */
    public function testLoginWithBadOTP(\ApiTester $I)
    {
        $user = $I->register(true);
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

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $I->getPassword(),
                'otp' => $totp->at(100)
            ]
        );

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(
            [
            'status' => 401
            ]
        );

        $I->seeResponseMatchesJsonType(
            [
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
            ]
        );
    }

    /**
     * Tests logging into the API with OTP enabled, but not provided
     *
     * @param ApiTester $I
     */
    public function testLoginWithoutOTP(\ApiTester $I)
    {
        $user = $I->register(true);
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

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/json');
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $I->getPassword()
            ]
        );

        $I->seeResponseIsJson();
        $I->seeResponseCodeIs(401);
        $I->seeResponseContainsJson(
            [
            'status' => 401
            ]
        );

        $I->seeResponseMatchesJsonType(
            [
            'data' => 'null',
            'status' => 'integer',
            'error' => [
                'message' => 'string',
                'code' => 'integer'
            ]
            ]
        );

        $I->seeResponseContainsJson(
            [
            'status' => 401,
            'data' => null,
            'error' => [
                'code' => 1
            ]
            ]
        );
    }

    /**
     * Sends a plain text request, and expects that the response is encrypted using our newly generated public key
     *
     * @param ApiTester
     */
    public function testAuthenticatePlainTextToEncryptedResponse(\ApiTester $I)
    {
        // Create a new user
        $user = $I->register(true);

        // Generate a new encryption key, mocking a request to /api/v1/server/otk
        $key = EncryptionKey::generate();
        
        $boxKp = sodium_crypto_box_keypair();
        $publicKey = \base64_encode(sodium_crypto_box_publickey($boxKp));
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
        
        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/vnd.25519+json');
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('x-pubkey', $publicKey);
        $I->haveHttpHeader('x-nonce', \base64_encode($nonce));

        $I->wantTo('Send an plain text response to authenticate and get an encrypted repsonse back');
        // The payload is now encrypted
        $I->sendPOST(
            $this->uri,
            [
                'email' => $I->getUser()->email,
                'password' => $I->getPassword(),
            ]
        );

        // We should get an encrypted HTTP 200 response back
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

    /**
     * Encrypts the response before sending it to the API for authenticate, then verifies the response itself is encrypted.
     *
     * @param ApiTester $I
     */
    public function testAuthenticateWithNcryptf(\ApiTester $I)
    {
        // Create a new user
        $user = $I->register(true);

        // Generate a new encryption key, mocking a request to /api/v1/server/otk
        $key = EncryptionKey::generate();
        
        $boxKp = sodium_crypto_box_keypair();

        // Send the hash id of the key we generated, and our public key along with the request
        $I->haveHttpHeader('x-hashid', $key->hash);
        $I->haveHttpHeader('Accept', 'application/vnd.ncryptf+json');
        $I->haveHttpHeader('Content-Type', 'application/vnd.ncryptf+json');
        $I->wantTo('Send an encrypted response to authenticate and get an encrypted response back with ncryptf');
        
        $request = new Request(
            sodium_crypto_box_secretkey($boxKp),
            \base64_decode($I->getTokens()->secret_sign_kp)
        );

        $payload = \base64_encode($request->encrypt(\json_encode([
            'email'         => $I->getUser()->email,
            'password'      => $I->getPassword()
        ]), $key->getBoxPublicKey()));

        // Send the encrypted response
        $I->sendPOST($this->uri, $payload);

        // We should get an encrypted HTTP 200 response back
        $I->seeResponseCodeIs(200);

        $r = new Response(
            sodium_crypto_box_secretkey($boxKp)
        );

        $response = $r->decrypt(
            \base64_decode($I->grabResponse())
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
     *
     * @param ApiTester $I
     */
    public function testAuthenticateWithEncryptedRequestAndEncryptedResponse(\ApiTester $I)
    {
        // Create a new user
        $user = $I->register(true);

        // Generate a new encryption key, mocking a request to /api/v1/server/otk
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

        $payload = \base64_encode($request->encrypt(\json_encode([
            'email'         => $I->getUser()->email,
            'password'      => $I->getPassword()
        ]), $key->getBoxPublicKey(), 1));
        
        $I->haveHttpHeader('x-nonce', \base64_encode($request->getNonce()));
        
        // Send the encrypted response
        $I->sendPOST($this->uri, $payload);

        // We should get an encrypted HTTP 200 response back
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
