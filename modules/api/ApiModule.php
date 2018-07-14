<?php

namespace api;

use yii\helpers\ArrayHelper;

class ApiModule extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'api\v1\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->controllerMap = ArrayHelper::merge($this->controllerMap, [
            'v1' => [
                'class' => 'api\v1\controllers\DefaultController',
            ],
        ]);

    }
}
