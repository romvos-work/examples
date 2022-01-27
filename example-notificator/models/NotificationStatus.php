<?php

namespace app\models;

use app\component\notificator\exceptions\ExceptionModelNotSaved;
use app\models\base\NotificationStatus as BaseModel;
use app\modules\user\models\User;
use DateTime;
use yii\db\ActiveQuery;

/**
 * Модель бизнес логики для {{%notification_status}}
 */
class NotificationStatus extends BaseModel
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * Метод установки статуса "прочитано"
     *
     * @param bool $isRead
     * @return bool
     * @throws ExceptionModelNotSaved
     */
    public function setRead(): bool
    {
        if (!$this->is_read) {
            $this->is_read = true;
            $this->read_at = date(self::DATE_FORMAT);
            if (!$this->save()) {
                throw new ExceptionModelNotSaved(self::class, $this->getErrors());
            }
        }

        return true;
    }

    /**
     * Метод установки статуса email отправлен
     *
     * @param bool $isRead
     * @return bool
     * @throws ExceptionModelNotSaved
     */
    public function setEmailSent(): bool
    {
        if (!$this->is_email_sent) {
            $this->is_email_sent = 1;
            $this->email_sent_at = date(self::DATE_FORMAT);
            if (!$this->save()) {
                throw new ExceptionModelNotSaved(self::class, $this->getErrors());
            }
        }

        return true;
    }

    /**
     * Метод получения запроса:
     *  пользователи, у которых есть непрочитанные сообщения,
     *  по которым еще не было отправлено уведомление на почту.
     *
     * @return ActiveQuery
     */
    public static function getQueryUsersWithUnreadNotifications(): ActiveQuery
    {
        $halfHourAgo = (new DateTime())->modify('-30 minutes');
        $subQueryUserHasUnreadNotifications = NotificationStatus::find()
            ->alias('s')
            ->select(['s.user_id'])
            ->leftJoin(['u' => User::tableName()], 's.user_id = u.id')
            ->where([
                's.is_read' => 0,
                's.is_email_sent' => 0,
            ])
            ->andWhere(['<', 'created_at', $halfHourAgo->format(NotificationStatus::DATE_FORMAT)])
            ->exists() ?: '1=0';

        $queryUserData = User::find()
            ->where($subQueryUserHasUnreadNotifications)
            ->andWhere(['is not', 'email', null]);

        return $queryUserData;
    }

    /**
     * Метод обновления статусов нотификации пользователя
     *
     * @param array $userIdList
     * @return int
     */
    public static function updateEmailHasSent(array $userIdList): int
    {
        return NotificationStatus::updateAll(
            [
                'is_email_sent' => 1,
                'email_sent_at' => date(NotificationStatus::DATE_FORMAT),
            ],
            ['in', 'user_id', $userIdList]
        );
    }

    /**
     * Метод получения запроса по связи
     *
     * @return ActiveQuery
     */
    public function getNotification(): ActiveQuery
    {
        return $this->hasOne(Notification::class, ['id' => 'notification_id']);
    }
}
