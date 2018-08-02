<?php

namespace common\models;

final class Token extends \yrc\models\redis\Token
{
    const REFRESH_TOKEN_CLASS = '\common\models\RefreshToken';
}
