<?php

namespace app\tests\unit;

use app\forms\Registration;
use Yii;

/**
 * Tests implementation of the Registration form
 */
class RegistrationTest extends \tests\codeception\Unit
{
    use \Codeception\Specify;
    
    /**
     * Tests various validation states
     */
    public function testValidator()
    {
        $faker = \Faker\Factory::create();
        $this->specify('tests required fields', function () use ($faker) {
            $form = new Registration;
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has password error', $form->getErrors())->hasKey('password');
            expect('form has password_verify error', $form->getErrors())->hasKey('password_verify');
        });

        $this->specify('tests email must have length > 4', function () use ($faker) {
            $form = new Registration;
            $form->email = $faker->email;
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
        });

        $this->specify('tests passwords must match', function () use ($faker) {
            $form = new Registration;
            $form->email = $faker->email;
            $form->password = 'badpass1';
            $form->password_verify = 'badpass2';
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has password_verify error', $form->getErrors())->hasKey('password_verify');
        });

        $this->specify('tests model validation', function () use ($faker) {
            $form = new Registration;
            $password = 'correct horse battery stable';
            $form->email = $faker->email;
            $form->password = $password;
            $form->password_verify = $password;
            $form->username = $faker->username;

            expect('form validate', $form->validate())->true();
        });
    }

    /**
     * Tests model registration
     */
    public function testRegistration()
    {
        $faker = \Faker\Factory::create();
        $this->specify('tests registration', function () use ($faker) {
            $form = new Registration;
            $password = $faker->password(22);
            $form->email = $faker->email;
            $form->username = $faker->username;
            $form->password = $password;
            $form->password_verify = $password;

            expect('form validates', $form->validate())->true();
            expect('form registers', $form->register())->true();
        });
    }
}