<?php

namespace tests\_support;

use ApiTester;
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
            $statusCode = \json_decode($I->grabResponse(), true)['status'];
            if (in_array($statusCode, [401, 405])) {
                $I->seeResponseCodeIs($statusCode);
            }

            expect('status code is not right', $statusCode)->notEquals(200);
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
}
