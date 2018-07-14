<?php

namespace api\v1\components;

use yii\log\FileTarget;


class MyFileTarget extends FileTarget
{
    public function formatMessage($message)
    {
        return "\n\n\n\n" . parent::formatMessage($message);
    }

}