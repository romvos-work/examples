<?php

namespace app\modules\notify;

use yii\base\Module;

/**
 * Модуль нотификации
 */
class NotifyModule extends Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\notify\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
    }
}
