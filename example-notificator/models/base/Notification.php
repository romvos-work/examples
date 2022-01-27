<?php

namespace app\models\base;

use app\modules\user\models\User;
use app\models\Room;
use Yii;

/**
 * This is the model class for table "{{%notification}}".
 *
 * @property int $id первичный ключ
 * @property int|null $room_id id комнаты, куда отправлять уведомление
 * @property string $text текст уведомления
 * @property int $type тип уведомления комната/юзер
 * @property string|null $params дополнительные параметры JSON
 * @property string $created_at
 * @property string $updated_at
 *
 * @property NotificationStatus[] $notificationStatuses
 * @property Room $room
 * @property User[] $users
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['room_id', 'type'], 'integer'],
            [['text', 'type'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['text', 'params'], 'string', 'max' => 255],
            [['room_id'], 'exist', 'skipOnError' => true, 'targetClass' => Room::className(), 'targetAttribute' => ['room_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'room_id' => 'Room ID',
            'text' => 'Text',
            'type' => 'Type',
            'params' => 'Params',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[NotificationStatuses]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotificationStatuses()
    {
        return $this->hasMany(NotificationStatus::className(), ['notification_id' => 'id']);
    }

    /**
     * Gets query for [[Room]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRoom()
    {
        return $this->hasOne(Room::className(), ['id' => 'room_id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id' => 'user_id'])->viaTable('{{%notification_status}}', ['notification_id' => 'id']);
    }
}
