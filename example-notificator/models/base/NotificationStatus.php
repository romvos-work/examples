<?php

namespace app\models\base;

use app\modules\user\models\User;

use Yii;

/**
 * This is the model class for table "{{%notification_status}}".
 *
 * @property int $id первичный ключ
 * @property int $notification_id id нотификации
 * @property int $user_id id юзера
 * @property int|null $is_read было ли прочитано пользоватвелем
 * @property int|null $is_email_sent был ли отправлен email
 * @property string|null $read_at дата прочтения уведомления
 * @property string|null $email_sent_at дата отправки email
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Notification $notification
 * @property User $user
 */
class NotificationStatus extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%notification_status}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['notification_id', 'user_id'], 'required'],
            [['notification_id', 'user_id'], 'integer'],
            [['is_read', 'is_email_sent'], 'boolean'],
            [['read_at', 'email_sent_at', 'created_at', 'updated_at'], 'safe'],
            [['notification_id', 'user_id'], 'unique', 'targetAttribute' => ['notification_id', 'user_id']],
            [['notification_id'], 'exist', 'skipOnError' => true, 'targetClass' => Notification::className(), 'targetAttribute' => ['notification_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'notification_id' => 'Notification ID',
            'user_id' => 'User ID',
            'is_read' => 'Is Read',
            'is_email_sent' => 'Is Email Sent',
            'read_at' => 'Read At',
            'email_sent_at' => 'Email Sent At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Notification]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNotification()
    {
        return $this->hasOne(Notification::className(), ['id' => 'notification_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}
