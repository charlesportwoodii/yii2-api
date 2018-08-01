<?php

namespace app\tests\unit;

use app\forms\ChangeEmail;
use Faker\Factory;
use Yii;

class ChangeEmailTest extends \tests\codeception\Unit
{
    use \Codeception\Specify;

    public function testValidate()
    {
        $this->specify(
            'test required fields', function () {
                $form = new ChangeEmail;
            
                expect('form does not validate', $form->validate())->false();
                expect('form has errors', $form->hasErrors())->true();
                expect('form has email error', $form->getErrors())->hasKey('email');
                expect('form has password error', $form->getErrors())->hasKey('password');
            }, [
            'throws' => '\yii\base\Exception'
            ]
        );

        $this->specify(
            'test a valid user is required', function () {
                $faker = Factory::create();
                $user = $this->register(true);
                $form = new ChangeEmail;
                $form->email = $faker->safeEmail;
                $form->password = $this->getPassword();
                $form->setUser($user);

                expect('form validates', $form->validate())->true();
                expect('form does not have errors', $form->hasErrors())->false();
            }
        );

        $this->specify(
            'tests that the email cannot be the users current email', function () {
                $faker = Factory::create();
                $user = $this->register(true);
                $form = new ChangeEmail;
                $form->email = $this->getUser()->email;
                $form->password = $this->getPassword();
                $form->setUser($user);

                expect('form does not validate', $form->validate())->false();
                expect('form has errors', $form->hasErrors())->true();
                expect('form has email error', $form->getErrors())->hasKey('email');
            }
        );
    }

    public function testChange()
    {
        $this->specify(
            'tests a user can actually change their email', function () {
                $faker = Factory::create();
                $user = $this->register(true);
                $form = new ChangeEmail;
                $form->email = $faker->safeEmail;
                $form->password = $this->getPassword();
                $form->setUser($user);

                expect('form validates', $form->validate())->true();
                expect('form does not have errors', $form->hasErrors())->false();

                expect('form changes user object', $form->change())->true();
                $this->getUser()->refresh();
                expect('user email has been changed', $this->getUser()->email)->equals($form->email);
            }
        );
    }
}