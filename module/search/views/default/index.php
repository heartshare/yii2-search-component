<?php
/**
 * @link https://github.com/himiklab/yii2-search-component
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

/** @var yii\web\View $this */
/** @var array $result */
/** @var string $query */

$query = yii\helpers\Html::encode($query);

$this->title = "Results for \"$query\"";
$this->params['breadcrumbs'] = ['Search', $this->title];

app\modules\search\SearchAssets::register($this);
$this->registerJs("jQuery('.search').highlight('{$query}');");

if (!empty($results)):
    foreach ($results as $result):
        ?>
        <h3><a href="<?= yii\helpers\Url::to($result->url, true) ?>"><?= $result->title ?></a></h3>
        <p class="search"><?= $result->body ?></p>
        <hr />
    <?php
    endforeach;
else:
    ?>
    <h3>The "<?= $query ?>" isn't found!</h3>
<?php
endif;
