<?php

namespace app\modules\notify\actions;

use app\component\notificator\Notificator;
use app\models\Room;
use Yii;
use yii\base\Action;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use Throwable;
use yii\web\ServerErrorHttpException;

/**
 * Endpoint для создания уведомления
 */
class NotificationsCreateForRoomAction extends Action
{
    /**
     * @param int $roomNumber
     * @return string
     * @throws BadRequestHttpException
     * @throws NotFoundHttpException
     * @throws ServerErrorHttpException
     * @throws Exception
     * @throws Throwable
     */
    public function run(int $roomNumber)
    {
        $room = Room::getRoomByNumber($roomNumber);
        if (empty($room)) {
            throw new NotFoundHttpException('room not found');
        }

        $text = Yii::$app->request->post('notification');
        $text = trim($text);
        if (empty($text)) {
            throw new BadRequestHttpException('notification is required');
        }

        try {
            /** @var Notificator $notificator */
            $notificator = Yii::$app->notificator;
            $notificator->notifyRoom($room->id, $text);
        } catch (Throwable $t) {
            $exception = YII_DEBUG
                ? $t
                : new ServerErrorHttpException('Service not available');

            throw $exception;
        }

        return 'ok';
    }
}
