<?php

$params = require(__DIR__ . '/params.php');
$localParamsFile = __DIR__ . '/params-local.php';
if (file_exists($localParamsFile)) {
    $localParams = require($localParamsFile);
    $params = array_merge($params, $localParams);
}



$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@api'   => '@app/modules/api',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '3ku_Pu4DRIpvGOgEX2a0RB6UPz6giumL',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'flushInterval' => 1,   // default is 1000
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                    'logFile' => '@app/runtime/logs/error.log',
                    'maxFileSize'  => 10240,
                    'maxLogFiles'  => 10,
                    'rotateByCopy' => true,
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info', 'trace'],
                    'logFile' => '@app/runtime/logs/debug.log',
                    'maxFileSize'  => 10240,
                    'maxLogFiles'  => 10,
                    'rotateByCopy' => true,
                    'logVars' => [],
                    'prefix' => function ($message) {return "";},
                ],
                [
                    'class' => 'api\v1\components\MyFileTarget',
                    'levels' => ['error', 'warning', 'info', 'profile', 'trace'],
                    'logFile' => '@app/runtime/logs/api.log',
                    'maxFileSize'  => 10240,
                    'maxLogFiles'  => 10,
                    'rotateByCopy' => true,
                    'categories' => ['api'],
                    'logVars' => [],
                    'prefix' => function ($message) {return "";},
                ],
            ],
        ],


        'db' => $params['db'],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                '/' => 'site/index',
                'api/<version:v\d+>/<action:\w+\/?>' => 'api/<version>/<action>',
            ],
        ],
    ],
    'modules' => [
        'api' => [
            'class' => 'api\ApiModule',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
