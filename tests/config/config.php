<?php return [
    'language' => 'en-US',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
            'fixtureDataPath' => '@tests/fixtures',
            'templatePath' => '@tests/templates',
            'namespace' => 'tests\fixtures',
        ],
    ],
    'components' => [
        'db' => []
    ]
];
