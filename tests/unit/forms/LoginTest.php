<?php

namespace app\tests\unit;

use app\forms\Login;
use OTPHP\TOTP;
use Faker\Factory;
use Yii;

class LoginTest extends \tests\codeception\TestCase
{
    use \Codeception\Specify;

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        \app\models\User::deleteAll();
    }

    public function testValidator()
    {
        $this->specify('test required fields', function () {
            $form = new Login;
            expect('form fails to validate', $form->validate())->false();
            expect('form has errors', $form->hasErrors())->true();
            expect('form has email error', $form->getErrors())->hasKey('email');
            expect('form has password error', $form->getErrors())->hasKey('password');
        });

        $this->specify('test login requires activated user', function () {
            $user = $this->createUser();
            $form = new Login;
            $form->load(['Login' => [
                'email' => $user->email,
                'password' => 'irrelevant' // The password is irrelevant for this test
            ]]);

            expect('User retrieval fails', $form->getUser())->equals(null);
        });

        $this->specify('test login with invalid password', function () {
            $user = $this->createUser(true);
            $form = new Login;
            $form->load(['Login' => [
                'email' => $user->email,
                'password' => 'irrelevant' // The password is irrelevant for this test
            ]]);

            expect('valid user is retrieved', $form->getUser())->notEquals(null);
            expect('form fails to validate', $form->validate())->false();
        });

        $this->specify('test login fails with OTP enabled', function () {
            $user = $this->createUser(true);
            $faker = \Faker\Factory::create();
            $password = $faker->password(24);
            $user->password = $password;
            
            expect('new password saves', $user->save())->true();
            expect('OTP is provisioned', $user->provisionOTP())->notEquals(false);
            expect('OTP is enabled', $user->enableOTP())->true();

            $form = new Login;
            $form->load(['Login' => [
                'email' => $user->email,
                'password' => $password
            ]]);

            expect('form validates', $form->validate())->false();
        });
    }

    public function testLogin()
    {
        $this->specify('test login', function () {
            $user = $this->createUser(true);
            $faker = \Faker\Factory::create();
            $password = $faker->password(24);
            $user->password = $password;
            
            expect('new password saves', $user->save())->true();

            $form = new Login;
            $form->load(['Login' => [
                'email' => $user->email,
                'password' => $password
            ]]);

            expect('form validates', $form->validate())->true();
            $details = $form->authenticate();
            expect('user authenticates', $details)->notEquals(null);
            expect('details have accessToken key', $details)->hasKey('accessToken');
            expect('details have refreshToken key', $details)->hasKey('refreshToken');
            expect('details have ikm key', $details)->hasKey('ikm');
            expect('details have expiration date', $details)->hasKey('expiresAt');
        });

        $this->specify('test login OTP', function () {
            $user = $this->createUser(true);
            $faker = \Faker\Factory::create();
            $password = $faker->password(24);
            $user->password = $password;
            
            expect('new password saves', $user->save())->true();
            expect('new password saves', $user->save())->true();
            expect('OTP is provisioned', $user->provisionOTP())->notEquals(false);
            expect('OTP is enabled', $user->enableOTP())->true();

            $totp = new TOTP(
                $user->email,
                $user->otp_secret,
                30,             // 30 second window
                'sha256',       // SHA256 for the hashing algorithm
                6               // 6 digits
            );

            $form = new Login;
            $form->load(['Login' => [
                'email' => $user->email,
                'password' => $password,
                'otp' => $totp->now()
            ]]);

            expect('form validates', $form->validate())->true();
            $details = $form->authenticate();
            expect('user authenticates', $details)->notEquals(null);
            expect('details have accessToken key', $details)->hasKey('accessToken');
            expect('details have refreshToken key', $details)->hasKey('refreshToken');
            expect('details have ikm key', $details)->hasKey('ikm');
            expect('details have expiration date', $details)->hasKey('expiresAt');
        });
    }
}