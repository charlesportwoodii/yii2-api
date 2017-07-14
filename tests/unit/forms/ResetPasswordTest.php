<?php

namespace app\tests\unit;

use app\forms\ResetPassword;
use Faker\Factory;
use OTPHP\TOTP;
use Base32\Base32;
use Yii;

use yrc\api\models\Code;

class ResetPasswordTest extends \app\tests\codeception\Unit
{
    use \Codeception\Specify;

    public function testInit()
    {
        $user = $this->createUser();
        $this->specify('test init scenario', function () use ($user) {
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_INIT]);
            $form->email = $user->email;

            expect('form validates', $form->validate())->true();
            expect('form does init', $form->reset())->true();
        });

        $this->specify('test init scenario (with invalid email)', function () {
            $user = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_INIT]);
            $form->email = $user->email;

            expect('form does not validate', $form->validate())->false();
            expect('form does not init', $form->reset())->false();
        });
    }

    public function testReset()
    {
        $user = $this->createUser();
        $this->specify('test reset scenario (with token)', function () use ($user) {
            // Generate a mock activation token
            $token = Base32::encode(\random_bytes(64));
            $code = new Code();
            $code->hash = hash('sha256', $token . '_reset_token');
            $code->user_id = $user->id;
            
            expect('code saves', $code->save())->true();

            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            $form->reset_token = $token;
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });

        $this->specify('test reset scenario (with user)', function () use ($user) {
            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->setUser($user);
            $token = Base32::encode(\random_bytes(64));
            $code = new Code();
            $code->hash = hash('sha256', $token . '_reset_token');
            $code->user_id = $user->id;
            
            expect('code saves', $code->save())->true();
            $form->reset_token = $token;
            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });
    }

    public function testResetWithOTP()
    {
        $user = $this->createUser();
        $this->specify('test that password cannot be reset if OTP is enabled', function () use ($user) {
            // Enable OTP on the account
            $user->provisionOTP();
            $user->enableOTP();

            expect('OTP is enabled', $user->isOTPEnabled())->true();

            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->setUser($user);
            $token = Base32::encode(\random_bytes(64));
            $code = new Code();
            $code->hash = hash('sha256', $token . '_reset_token');
            $code->user_id = $user->id;
            
            expect('code saves', $code->save())->true();

            $form->reset_token = $token;
            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            
            expect('form validates', $form->validate())->false();
            expect('form has OTP error', $form->getErrors())->hasKey('otp');
        });

        $this->specify('tests password reset with valid OTP code', function () use ($user) {
            // Enable OTP on the account
            $user->provisionOTP();
            $user->enableOTP();

            expect('OTP is enabled', $user->isOTPEnabled())->true();

            $totp = TOTP::create(
                $user->otp_secret,
                30,
                'sha256',
                6
            );

            $totp->setLabel($user->username);

            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET]);
            $form->setUser($user);
            $token = Base32::encode(\random_bytes(64));
            $code = new Code();
            $code->hash = hash('sha256', $token . '_reset_token');
            $code->user_id = $user->id;
            
            expect('code saves', $code->save())->true();

            $form->reset_token = $token;
            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            $form->otp = $totp->now();
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });
    }

    public function testAuthenticatedResetScenario()
    {
        $user = $this->createUser();
        $this->specify('test authenticated password reset', function () use ($user) {
            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET_AUTHENTICATED]);
            $form->setUser($user);
            $form->user_id = $user;

            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            $form->old_password = $this->getPassword();
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });

        $user = $this->createUser();
        $this->specify('test authenticated password reset with OTP', function () use ($user) {
            $faker = Factory::create();
            $form = new ResetPassword(['scenario' => ResetPassword::SCENARIO_RESET_AUTHENTICATED]);

            // Enable OTP on the account
            $user->provisionOTP();
            $user->enableOTP();

            expect('OTP is enabled', $user->isOTPEnabled())->true();

            $totp = TOTP::create(
                $user->otp_secret,
                30,
                'sha256',
                6
            );

            $totp->setLabel($user->username);

            $form->setUser($user);
            $form->user_id = $user;

            $form->password = $faker->password(24);
            $form->password_verify = $form->password;
            $form->old_password = $this->getPassword();
            $form->otp = $totp->now();
            
            expect('form validates', $form->validate())->true();
            expect('form resets', $form->reset())->true();
        });
    }
}