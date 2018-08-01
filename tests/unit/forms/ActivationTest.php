<?php

namespace app\tests\unit;

use app\forms\Activation;
use Base32\Base32;
use Yii;

use yrc\models\redis\Code;

class ActivationTest extends \tests\codeception\Unit
{
    use \Codeception\Specify;

    /**
     * Tests activation
     */
    public function testActivation()
    {
        $this->specify(
            'test invalid activation token', function () {
                $user = $this->register();
            
                $form = new Activation;
                $form->load(
                    ['Activation' => [
                    'activation_code' => 'foo'
                    ]]
                );

                expect('form does not validate', $form->validate())->false();
            }
        );

        $this->specify(
            'test valid activation token', function () {
                $user = $this->register();
                $token = Base32::encode(\random_bytes(64));
                $code = new Code();
                $code->hash = hash('sha256', $token . '_activation_token');
                $code->user_id = $this->getUser()->id;
            
                expect('code saves', $code->save())->true();

                $form = new Activation;
                $form->load(
                    ['Activation' => [
                    'activation_code' => $token
                    ]]
                );

                expect('form validates', $form->validate())->true();
                expect('user activates', $form->activate())->true();
                // Refresh the model data
                $this->getUser()->refresh();
                expect('user is activated', $this->getUser()->isActivated())->true();

                $token = Yii::$app->cache->get($token);

                expect('token is wiped', !$token)->true();
            }
        );
    }
}