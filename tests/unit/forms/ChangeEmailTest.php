<?php

namespace app\tests\unit;

use app\forms\ChangeEmail;
use Base32\Base32;
use Yii;

class ChangeEmailTest extends \tests\codeception\TestCase
{
    use \Codeception\Specify;

    protected function _before()
    {
        parent::_before();
        Yii::$app->cache->flush();
        \app\models\User::deleteAll();
    }
}