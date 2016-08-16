<?php

namespace tests\codeception;

class TestCase extends \yii\codeception\TestCase
{
    public $appConfig = "@tests/config/unit.php";

    protected function _before()
    {
        $this->mockApplication();
    }
    
    protected function _after()
    {
        $this->mockApplication();
    }
}
