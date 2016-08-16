<?php

namespace app\tests\unit;

use app\forms\Activation;
use app\forms\Registration;
use Base32\Base32;
use Faker\Factory;
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
     * Creates a new user
     * @return User
     */
    private function registerUser()
    {
        $faker = \Faker\Factory::create();
        $form = new Registration;

        $password = $faker->password(24);
        
        $form->email = $faker->email;
        $form->password = $password;
        $form->password_verify = $password;

        expect('form validates', $form->validate())->true();
        expect('user can be registered', $form->register())->true();

        $config = require  Yii::getAlias('@app') . '/config/loader.php';
        $userClass = $config['yii2']['user'];
        
        return $userClass::findOne(['email' => $form->email]);
    }

    /**
     * Tests activation
     */
    public function testActivation()
    {
        $this->specify('test invalid activation token', function () {
            $user = $this->registerUser();
            
            $form = new Activation;
            $form->load(['Activation' => [
                'activation_code' => 'foo'
            ]]);

            expect('form does not validate', $form->validate())->false();
        });

        $this->specify('test valid activation token', function () {
            $user = $this->registerUser();
            $token = Base32::encode(\random_bytes(64));
            Yii::$app->cache->set($token, [
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