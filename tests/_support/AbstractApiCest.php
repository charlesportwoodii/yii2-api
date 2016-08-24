<?php

namespace tests\_support;

use ApiTester;

use app\forms\Registration;
use app\models\Token;
use Faker\Factory;

use Yii;

/**
 * Supporter class for API Cests
 * @class ApiCest
 */
abstract class AbstractApiCest
{
    /**
     * The URI to implement
     * @var string $uri
     */
    protected $uri = null;
    
    /**
     * An array of HTTP verbs that should return an HTTP 405
     * @var array $blockedVerbs
     */
    protected $blockedVerbs = [];

    /**
     * An array of HTTP verbs that should be allowed via CORS
     * @var array $allowedVerbs
     */
    protected $allowedVerbs = [];
    
    /**
     * Token data
     * @var Token $tokens
     */
    protected $tokens = [];
    
    /**
     * Instance of user to reduce lookups
     * @var User
     */
    protected $user;
    
    /**
     * Before the test, clear all users form the database, and flush the cache to ensure a clean slate
     * @param ApiTester
     */
    public function _before(\ApiTester $I)
    {
        Yii::$app->yrc->userClass::deleteAll();
        Yii::$app->cache->flush();
        
        // Verify a URI is set
        expect('uri is set', $this->uri)->notEquals(null);
    }
    
    /**
     * Tests that any set blocked verbs returns an HTTP 405
     * @param ApiTester $I
     */
    public function testBlockedHttpVerbs(\ApiTester $I)
    {
        foreach ($this->blockedVerbs as $verb) {
            $method = 'send' . $verb;
            $I->$method($this->uri);
            $I->seeResponseIsJson();
            $I->seeResponseCodeIs(405);
        }
    }

    /**
     * Tests HTTP OPTIONS
     * @param ApiTester
     */
    public function testOptions(\ApiTester $I)
    {
        $I->wantTo('test HTTP OPTIONS');
        $I->haveHttpHeader('Access-Control-Request-Method', 'options');
        $I->sendOPTIONS($this->uri);

        $this->allowedVerbs = array_merge($this->allowedVerbs, ['options']);
        $acam = explode(', ', $I->grabHttpHeader('access-control-allow-methods'));
        foreach ($this->allowedVerbs as $verb) {
            expect('HTTP OPTIONS is in access-control-allow-methods header', in_array($verb, $acam))->true();
        }

        $I->seeResponseCodeIs(204);
        $I->seeResponseEquals('');

        return $I;
    }

    /**
     * Register a new user for testing
     * @return bool
     */
    protected function register($activate = true, \ApiTester $I = null)
    {
        $faker = Factory::create();
        $form = new Registration;
        $form->email = $faker->email;
        $form->password = $faker->password(20);
        $form->password_verify = $form->password;

        expect('form registers', $form->register())->true();
        $this->user = Yii::$app->yrc->userClass::findOne(['email' => $form->email]);

        if ($activate === true) {
            $this->user->activate();
        }

        expect('user is not null', $this->user !== null)->true();
        $this->tokens = Token::generate($this->user->id);
        if ($I !== null) {
            $I->addTokens($this->tokens);
        }
        
        return $form->password;
    }
}
