<?php

namespace app\tests\unit;

use app\forms\Activation;
use Base32\Base32;
use Yii;

class ActivationTest extends \tests\codeception\TestCase
{
    use \Codeception\Specify;

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        \app\models\User::deleteAll();
    }

    /**
     * Tests activation
     */
    public function testActivation()
    {
        $this->specify('test invalid activation token', function () {
            $user = $this->createUser();
            
            $form = new Activation;
            $form->load(['Activation' => [
                'activation_code' => 'foo'
            ]]);

            expect('form does not validate', $form->validate())->false();
        });

        $this->specify('test valid activation token', function () {
            $user = $this->createUser();
            $token = Base32::encode(\random_bytes(64));
            Yii::$app->cache->set(hash('sha256', $token . '_activation_token'), [
                'id' => $user->id
            ]);
            $form = new Activation;
            $form->load(['Activation' => [
                'activation_code' => $token
            ]]);

            expect('form validates', $form->validate())->true();
            expect('user activates', $form->activate())->true();
            // Refresh the model data
            $user->refresh();
            expect('user is activated', $user->isActivated())->true();

            $token = Yii::$app->cache->get($token);

            expect('token is wiped', !$token)->true();
        });
    }
}