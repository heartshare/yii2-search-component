<?php
/**
 * @link https://github.com/himiklab/yii2-search-component
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace app\modules\search\controllers;

use Yii;
use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex($q = '')
    {
        /** @var \himiklab\search\Search $search */
        $search = Yii::$app->search;
        $data = $search->find($q);

        return $this->render('index', $data);
    }

    // of course, this function should be in the console part of the application!
    public function actionIndexing()
    {
        /** @var \himiklab\search\Search $search */
        $search = Yii::$app->search;
        $search->index();
    }
}
