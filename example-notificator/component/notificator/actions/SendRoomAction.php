<?php

namespace app\component\notificator\actions;

use app\component\notificator\Notificator;
use app\component\notificator\exceptions\ExceptionNotificatorCommon;
use Yii;
use yii\base\Action;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * Endpoint для нотификации всех пользователей в комнате
 */
class SendRoomAction extends Action
{
    /**
     * @OA\Post(
     *     tags={"проставить тэги"},
     *     path="проставить путь",
     *     summary="отправка уведомления всем пользователям в комнате",
     *     @OA\RequestBody(
     *          description="json с данными группы",
     *          @OA\JsonContent(
     *              example={
     *                  "roomId": 1,
     *                  "text": 'text',
     *                  "params": '{"param1":"some"}'
     *              }
     *          ),
     *          required=true
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Массив с инф о группе",
     *          @OA\Schema(
     *              type="array"
     *          )
     *     ),
     *     @OA\Response(
     *          response=500,
     *          description="Сервис не доступен",
     *     ),
     * )
     */
    /**
     * @param int $roomId
     * @param string $text
     * @param string $params
     * @return string
     * @throws BadRequestHttpException
     * @throws ExceptionNotificatorCommon
     * @throws ServerErrorHttpException
     */
    public function run(int $roomId, string $text, string $params = '')
    {
        if (empty($roomId) || empty($text)) {
            throw new BadRequestHttpException('missing required fields');
        }

        $params = Json::decode($params) ?: [];

        /** @var Notificator $notificator */
        $notificator = Yii::$app->notificator;
        if (!$notificator->notifyRoom($roomId, $text, $params)) {
            throw new ServerErrorHttpException('Сервис недоступен');
        }

        return 'ok';
    }
}
