<?php

namespace app\modules\notify\actions;

use app\component\notificator\Notificator;
use app\modules\user\models\User;
use Yii;
use Throwable;
use yii\base\Action;
use yii\web\ServerErrorHttpException;

/**
 * Endpoint для получения списка нотификаций
 *  для конкретного пользователя
 */
class NotificationsGetListAction extends Action
{
    /** @var User */
    public $user;

    /**
     * @return array
     */
    public function run(int $page = 1, int $limit = 20)
    {
        $result = [];
        try {
            /** @var Notificator $notificator */
            $notificator = Yii::$app->notificator;
            $result = $notificator->getUsersNotifications($this->user, $page, $limit);
        } catch (Throwable $t) {
            $exception = YII_DEBUG
                ? $t
                : new ServerErrorHttpException('Server is not available');

            throw $exception;
        }

        return $result;
    }
}
