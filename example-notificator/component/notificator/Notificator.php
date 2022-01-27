<?php

namespace app\component\notificator;

use app\component\notificator\exceptions\ExceptionModelNotFound;
use app\component\notificator\exceptions\ExceptionModelNotSaved;
use app\component\notificator\exceptions\ExceptionNotificatorCommon;
use app\models\Notification;
use app\models\NotificationStatus;
use app\modules\user\models\User;
use app\component\services\control\ServiceControlNotifications;
use app\component\services\control\exceptions\ExceptionRequestFailed;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\db\Exception;
use Throwable;

/**
 * Компонент для рассылки уведомлений
 * Типы уведомлений
 *  - общие: всем пользователям
 */
class Notificator extends Component
{
    private const CACHE_DURATION_NOTIFICATIONS_COUNT = 3600;

    /**
     * Метод оповещение всех пользователей в комнате
     *
     * @param int $roomId
     * @param string $text
     * @param array $params
     * @return bool
     * @throws Exception
     * @throws ExceptionModelNotFound
     * @throws ExceptionModelNotSaved
     * @throws ExceptionNotificatorCommon
     * @throws ExceptionRequestFailed
     */
    public function notifyRoom(int $roomId, string $text, array $params = []): bool
    {
        $controlUserData = ServiceControlNotifications::getUserDataFromRoom($roomId);
        $tokenList = ArrayHelper::getColumn($controlUserData, 'token');
        $userData = User::find()
            ->select(['id'])
            ->asArray()
            ->indexBy('id')
            ->where(['token' => $tokenList])
            ->all();

        $userIdList = array_keys($userData);
        if (empty($userIdList)) {
            throw new ExceptionModelNotFound('no users found, skip notifying', $tokenList);
        }

        $notification = new Notification([
            'room_id' => $roomId,
            'type' => Notification::TYPE_PUBLIC,
            'text' => $text,
            'params' => Json::encode($params, true),
        ]);

        if (!$notification->save()) {
            throw new ExceptionModelNotSaved(Notification::class, $notification->getErrors());
        }

        $notification->initNotificationStatuses($userIdList);

        ServiceControlNotifications::notifyUsers(
            ArrayHelper::getColumn($controlUserData, 'connId'),
            $notification
        );

        return true;
    }

    /**
     * Метод отправки уведомления конкретного пользователя
     *
     * @param User $user
     * @param string $text
     * @param array $params
     * @return bool
     * @throws Exception
     * @throws ExceptionModelNotSaved
     * @throws ExceptionNotificatorCommon
     */
    public function notifyUser(User $user, string $text, array $params): bool
    {
        $notification = new Notification([
            'type' => Notification::TYPE_PRIVATE,
            'text' => $text,
            'params' => Json::encode($params, true),
        ]);

        if (!$notification->save()) {
            throw new ExceptionModelNotSaved(Notification::class, $notification->getErrors());
        }


        return $notification->initNotificationStatuses([
            ['id' => $user->id],
        ]);
    }

    /**
     * Метод отправки email'ов пользователям,
     *  у которых есть непрочитанные сообщения
     * @return array
     */
    public function sendEmailNotificationUnread()
    {
        $userList = NotificationStatus::getQueryUsersWithUnreadNotifications();

        $batchSize = 50;
        $usersToUpdateStatuses = [];
        $countEmailSent = 0;
        $countStatusesToUpdate = 0;
        foreach ($userList->batch($batchSize) as $batch) {
            /** @var User $user */
            foreach ($batch as $user) {
                if ($this->sendEmail($user)) {
                    $countEmailSent++;
                } else {
                    continue;
                }

                $usersToUpdateStatuses[] = $user->id;
                $countStatusesToUpdate++;
            }
        }

        $countStatusesUpdated = NotificationStatus::updateEmailHasSent($usersToUpdateStatuses);

        return [
            'countEmailSent' => $countEmailSent,
            'countStatusesToUpdate' => $countStatusesToUpdate,
            'countStatusesUpdated' => $countStatusesUpdated,
        ];
    }

    /**
     * Метод отправки email'а пользователю
     *
     * @param User $user
     * @return bool
     */
    private function sendEmail(User $user): bool
    {
        // @todo убрать тестовый код, для отправки email'ов
        return true;
        try {
            $compose = Yii::$app->mailer
                ->compose('email_notifications_unread', [
                    'fullname' => $user->fio,
                ])
                ->setFrom(['postmaster@whenspeak.ru' => 'WhenSpeak'])
                ->setTo($user->email)
                ->setSubject('у вас есть непрочитанные уведомления');

            return $compose->send();
        } catch (Throwable $t) {
            return false;
        }
    }

    /**
     * @param User $user
     * @param int $page
     * @param int $limit
     * @return array <code>[
     *    'notifications' => array,
     *    'meta' => [
     *      'countUnread' => int,
     *      'countTotal' => int,
     *      'page' => int,
     *      'limit' => int,
     *    ],
     * ]</code>
     */
    public function getUsersNotifications(User $user, int $page = 1, int $limit = 30)
    {
        $key = $this->getCacheKeyNotificationsCount($user->id);
        $countNotifications = Yii::$app->cache->getOrSet($key, function () use ($user) {
            $queryCountTotal = Notification::getQueryNotificationsForUser($user);
            $queryCountUnread = Notification::getQueryNotificationsForUser($user, false);

            return [
                'countUnread' => $queryCountUnread->count(),
                'countTotal' => $queryCountTotal->count(),
            ];
        }, self::CACHE_DURATION_NOTIFICATIONS_COUNT);

        $queryNotifications = Notification::getQueryNotificationsForUser($user);
        $notifications = $queryNotifications->offset(--$page * $limit)
            ->all() ?: [];

        return [
            'notifications' => $notifications,
            'meta' => array_merge($countNotifications, [
                'page' => $page,
                'limit' => $limit,
            ]),
        ];
    }

    /**
     * Метод сброса кеша кол-ва уведомлений для пользователя
     *
     * @param User $user
     * @return void
     */
    private function clearCacheNotificationsCount(User $user): void
    {
        $key = $this->getCacheKeyNotificationsCount($user->id);
        Yii::$app->cache->delete($key);
    }

    /**
     * Получение ключа кеша кол-во уведомлений у пользователя.
     *
     * @param $userId
     * @return string
     */
    private function getCacheKeyNotificationsCount($userId): string
    {
        return 'notify.notifications.count.' . $userId;
    }
}
