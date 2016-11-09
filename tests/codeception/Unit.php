<?php

namespace app\tests\codeception;

use \Codeception\Test\Unit as UnitTest;

use Faker\Factory;
use app\forms\Registration;
use Yii;

class Unit extends UnitTest
{
    private $password;

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        \app\models\User::deleteAll();
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Creates a new user
     * @return User
     */
    protected function createUser($activate = false)
    {
        $faker = Factory::create();
        $form = new Registration;

        $password = $faker->password(24);
        
        $form->email = $faker->safeEmail;
        $form->username = $faker->username;
        $form->password = $password;
        $form->password_verify = $password;

        expect('form validates', $form->validate())->true();
        expect('user can be registered', $form->register())->true();

        $config = require  Yii::getAlias('@app') . '/config/loader.php';
        $userClass = $config['yii2']['user'];
        
        $user = $userClass::findOne(['email' => $form->email]);

        if ($activate === true) {
            expect('user activates', $user->activate())->true();
            $user->refresh();
            expect('user is activated', $user->isActivated())->true();
        }

        $this->password = $password;

        return $user;
    }
}