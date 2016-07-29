<?php

namespace app\tests\unit;

use yrc\api\forms\Registration;
use Yii;

class RegistrationTest extends \tests\codeception\TestCase
{
    use \Codeception\Specify;

    /**
     * Tests various validation states
     */
    public function testValidator()
    {
        $faker = \Faker\Factory::create();
        $this->specify('tests required fields', function() use ($faker) {
            $form = new Registration;
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has username error', $form->getErrors())->hasKey('username');
            expect('form has password error', $form->getErrors())->hasKey('password');
            expect('form has password_verify error', $form->getErrors())->hasKey('password_verify');
        });

        $this->specify('tests username must have length > 4', function() use ($faker) {
            $form = new Registration;
            $form->username = 'tet';
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has username error', $form->getErrors())->hasKey('username');
        });

        $this->specify('tests passwords must match', function() use ($faker) {
            $form = new Registration;
            $form->username = $faker->username;
            $form->password = 'badpass1';
            $form->password_verify = 'badpass2';
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has password_verify error', $form->getErrors())->hasKey('password_verify');
        });

        $this->specify('tests password entrophy', function() use ($faker) {
            $form = new Registration;
            $form->username = $faker->username;
            $form->password = 'weakpass';
            $form->password_verify = 'weakpass';
            expect('form fails to validate', $form->validate())->false();

            expect('form has errors', $form->hasErrors())->true();
            expect('form has password error', $form->getErrors())->hasKey('password');
        });

        $this->specify('tests model validation', function() use ($faker) {
            $form = new Registration;
            $password = 'correct horse battery stable';
            $form->username = $faker->username;
            $form->password = $password;
            $form->password_verify = $password;
            expect('form validate', $form->validate())->true();
        });
    }

    /**
     * Tests model registration
     */
    public function testRegistration()
    {
        $faker = \Faker\Factory::create();
        $this->specify('tests registration', function() use ($faker) {
            $form = new Registration;
            $password = 'correct horse battery stable';
            $form->username = $faker->username;
            $form->password = $password;
            $form->password_verify = $password;

            expect('form validates', $form->validate())->true();
            expect('form registers', $form->register())->true();
        });
    }
}