<?php

namespace app\tests\unit;

use app\forms\ResetPassword;
use Faker\Factory;
use Base32\Base32;
use Yii;

class ResetPasswordTest extends \tests\codeception\TestCase
{
    use \Codeception\Specify;

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        \app\models\User::deleteAll();
    }

    /**
     * Tests the scenarios
     */
    public function testScenario()
    {
        $user = $this->createUser();
        $this->specify('test init scenario', function () use ($user) {
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_INIT]);
            $form->email = $user->email;

            expect('form validates', $form->validate())->true();
            expect('form does init', $form->initReset())->true();
        });

        $this->specify('test init scenario (with invalid email)', function () {
            $user = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_INIT]);
            $form->email = $user->email;

            expect('form does not validate', $form->validate())->false();
            expect('form does not init', $form->initReset())->false();
        });

        $this->specify('test reset scenario (with token)', function () use ($user) {
            // Generate a mock activation token
            $token = Base32::encode(\random_bytes(64));
            Yii::$app->cache->set(hash('sha256', $token . '_reset_token'), [
                'id' => $user->id
            ]);

            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->password = $faker->password;
            $form->password_verify = $form->password;
            $form->reset_token = $token;
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });

        $this->specify('test reset scenario (with user)', function () use ($user) {
            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->setUser($user);
            $form->reset_token = 1;
            $form->password = $faker->password;
            $form->password_verify = $form->password;
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });
    }
}