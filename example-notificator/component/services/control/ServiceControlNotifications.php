<?php

namespace app\component\services\control;

use app\models\Notification;
use app\component\services\control\exceptions\ExceptionRequestFailed;
use Yii;
use yii\helpers\Json;

/**
 * Сервис предоставляет данные с сервера Control:
 *  - получение токенов пользователей, подключенных к комнате
 */
class ServiceControlNotifications
{
    /**
     * Метод получения списка токенов пользователей,
     *  которые подключены к комнате
     *
     * @param int $roomId
     * @return array
     */
    public static function getUserDataFromRoom(int $roomId)
    {
        $url = implode('/', [
            Yii::$app->params['controll_url'],
            'get-room-users',
            $roomId,
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if ($response === false) {
            throw new ExceptionRequestFailed('getUserDataFromRoom', $info);
        } else {
            $response = Json::decode($response, true);
        }

        return $response;
    }

    /**
     *
     * @param array $connectionIds
     * @param Notification $notification
     * @return bool
     * @throws ExceptionRequestFailed
     */
    public static function notifyUsers(array $connectionIds, Notification $notification): bool
    {
        $url = implode('/', [
            Yii::$app->params['controll_url'],
            'direct-message',
        ]);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            json_encode([
                'connIds' => $connectionIds,
                'directMessage' => [
                    'type' => '',
                    'text' => $notification->text,
                ],
            ])
        );
        $response = curl_exec($curl);
        curl_close($curl);

        $response = Json::decode($response, true);
        if (empty($response['result'])) {
            throw new ExceptionRequestFailed('notifyUsers: ', $response);
        }

        return true;
    }
}
