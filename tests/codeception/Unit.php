<?php

namespace tests\codeception;

use \Codeception\Test\Unit as UnitTest;

use Faker\Factory;
use app\forms\Registration;
use Yii;

class Unit extends UnitTest
{
    use \tests\_support\traits\UserTrait;

    /**
     * Codeception _before test
     */
    protected function _before()
    {
        parent::_before();
    }
}
