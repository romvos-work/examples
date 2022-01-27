<?php

namespace app\models;

use app\component\notificator\exceptions\ExceptionModelNotFound;
use app\component\notificator\exceptions\ExceptionModelNotSaved;
use app\component\notificator\exceptions\ExceptionNotificatorCommon;
use app\models\base\Notification as BaseModel;
use app\modules\user\models\User;
use Yii;
use yii\db\ActiveQuery;
use yii\db\Exception;

/**
 * Модель бизнес логики для {{%notification}}
 */
class Notification extends BaseModel
{
    public const TYPE_PUBLIC = 1;
    public const TYPE_PRIVATE = 2;

    /**
     * Метод создаёт инициализирует состояние нотификации "не прочитано" для пользователей
     *
     * @param array $userList
     * @return bool
     * @throws Exception
     * @throws ExceptionNotificatorCommon
     */
    public function initNotificationStatuses(array $userList): bool
    {
        if (empty($userList)) {
            throw new ExceptionNotificatorCommon('initMessageUnread: user list is empty');
        }

        $countData = 0;
        $data = [];
        foreach ($userList as $user) {
            $data[] = [
                'notification_id' => $this->id,
                'user_id' => $user['id'],
            ];
            $countData++;
        }

        $result = Yii::$app->db->createCommand()
            ->batchInsert(
                NotificationStatus::tableName(),
                array_keys(current($data)),
                $data
            )
            ->execute();

        if ($result != $countData) {
            throw new ExceptionNotificatorCommon('initMessageUnread: some rows has not saved');
        }

        return true;
    }

    /**
     * Метод устанавливает статус ноификции прочитано для конкретного юзера
     *
     * @param User $user
     * @return bool
     * @throws ExceptionModelNotFound
     * @throws ExceptionModelNotSaved
     */
    public function setRead(User $user): bool
    {
        /** @var NotificationStatus $status */
        $status = $this->getNotificationStatuses()
            ->where(['user_id' => $user->id])
            ->one();

        if (!$status) {
            throw new ExceptionModelNotFound('NotificationStatus', [
                'notification_id' => $this->id,
                'user_id' => $user->id,
            ]);
        }

        return $status->setRead();
    }

    /**
     * Получение запроса: список уведомлений для конкретного пользователя
     *
     * @param User $user
     * @param bool|null $isRead
     * @return ActiveQuery
     */
    public static function getQueryNotificationsForUser(
        User $user,
        ?bool $isRead = null
    ): ActiveQuery
    {
        $subQueryRoom = Room::find()
            ->select(['id'])
            ->where(['number' => $user->current_room]);

        $subQueryNotificationStatus = NotificationStatus::find()
            ->select(['notification_id'])
            ->asArray()
            ->where([
                'user_id' => $user->id,
                'room_id' => $subQueryRoom,
            ])
            ->andFilterWhere(['is_read' => $isRead])
            ->orderBy([
                'created_at' => SORT_DESC,
                'is_read' => SORT_ASC,
            ]);

        return Notification::find()
            ->where(['id' => $subQueryNotificationStatus]);
    }

    /**
     * Метод получения запроса по связи
     *
     * @return ActiveQuery
     */
    public function getNotificationStatuses()
    {
        return $this->hasMany(NotificationStatus::class, ['notification_id' => 'id']);
    }
}
