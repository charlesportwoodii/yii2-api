<?php

namespace tests\_support\traits;

use app\models\User;
use app\models\Token;
use app\forms\Registration;
use Faker\Factory;
use Yii;

trait UserTrait
{
    /**
     * Instance of user to reduce lookups
     * @var User
     */
    protected $user;
    
    /**
     * The tokens
     * @var array
     */
    protected $tokens = [];

    /**
     * The user's password
     * @var string
     */
    protected $password;

    /**
     * Retrieves the user
     * @return User
     */
    public function getUser()
    {
        $this->user->refresh();
        return $this->user;
    }

    /**
     * Retrieves the token
     * @return array
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Sets tokens
     * @param array $tokens
     */
    public function addTokens($tokens)
    {
        if (isset($tokens['access_token'])) {
            $this->tokens = $tokens;
        } else {
            $this->tokens = [
                'access_token'   => $tokens['access_token'],
                'refresh_token'  => $tokens['refresh_token'],
                'ikm'            => $tokens['ikm'],
            ];
        }
    }

    /**
     * Register a new user for testing
     * @return bool
     */
    public function register($activate = true, $withTokens = true)
    {
        $faker = Factory::create();
        $form = new Registration;
        $form->email = $faker->safeEmail;
        $form->username = $faker->username(10);
        $form->password = $faker->password(20);
        $form->password_verify = $form->password;

        expect('form registers', $form->register())->true();
        $this->user = Yii::$app->yrc->userClass::findOne(['email' => $form->email]);

        if ($activate === true) {
            $this->user->activate();
        }

        expect('user is not null', $this->user !== null)->true();
        if ($withTokens) {
            $this->tokens = Token::generate($this->user->id, null);
            $this->addTokens($this->tokens);
        }
        
        $this->password = $form->password;
        return $this->user;
    }

    public function addPermissions($permissions = [])
    {
        $am = Yii::$app->authmanager;
        foreach ($permissions as $permission) {
            $am->assign($am->getPermission($permission), $this->getUser()->id);
        }
    }
}