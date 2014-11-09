<?php
/**
 * @link https://github.com/himiklab/yii2-search-component
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace himiklab\search;

/**
 * @author HimikLab
 * @package himiklab\search
 */
interface SearchInterface
{
    public function getSearchTitle();

    public function getSearchBody();

    public function getSearchUrl();
}
