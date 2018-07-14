<?php

namespace api\v1\controllers;

use yii\web\Controller;
use api\v1\components\JsonRpcIncomingCall;

/**
 * Default controller for the `api` module
 */
class DefaultController extends Controller
{
    public function beforeAction($action)
    {
        // Disable CSRF validation
        // @see https://yii2-cookbook.readthedocs.io/csrf/
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }



    public function actionTree()
    {
        $api = 'api\v1\components\TreeApi';
        $callProcessor = new JsonRpcIncomingCall();
        $callProcessor->process($api);
    }
}
