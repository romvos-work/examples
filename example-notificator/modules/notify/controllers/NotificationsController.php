<?php

namespace app\modules\notify\controllers;

use app\component\access\ApiAccessControl;
use app\component\Rules;
use app\modules\notify\actions\NotificationsCreateForRoomAction;
use app\modules\notify\actions\NotificationsGetListAction;
use Yii;
use yii\base\Controller;
use yii\filters\Cors;
use yii\filters\VerbFilter;

/**
 * Контроллер для endpoint'ов обеспечивающих логику уведомлений
 */
class NotificationsController extends Controller
{
    /**
     * @return array
     */
    public function behaviors(): array
    {
        $corsRules = Yii::$app->params['cors_rules'];
        return [
            'corsFilter' => [
                'class' => Cors::class,
                'cors' => $corsRules
            ],
            'access' => [
                'class' => ApiAccessControl::class,
                'rules' => [
                    Rules::getXTokenRule([
                        'create-for-room',
                        'get-list',
                    ]),
                    [
                        'allow' => true,
                        'actions' => [],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-for-room' => ['post'],
                    'get-list' => ['get'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function actions(): array
    {
        return array_merge(parent::actions(), [
            'create-for-room' => NotificationsCreateForRoomAction::class,
            'get-list' => NotificationsGetListAction::class,
        ]);
    }
}
