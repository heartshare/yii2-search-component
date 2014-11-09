Yii2 Zend Lucene Search Component
========================
Zend Lucine search component for Yii2.

Installation
------------
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

* Either run

```
php composer.phar require --prefer-dist "himiklab/yii2-search-component" "*"
```

or add

```json
"himiklab/yii2-search-component" : "*"
```

to the require section of your application's `composer.json` file.

* Add a new component in `components` section of your application's configuration file, for example:

```php
'components' => [
    'search' => [
        'class' => 'himiklab\search\Search',
        'models' => ['app\modules\page\models\Page'],
    ],
    // ...
],
```

* Implements himiklab\search\SearchInterface in your models, for example:

```php
use himiklab\search\SearchInterface;

class Page extends ActiveRecord implements SearchInterface
{
    // ...

    public function getSearchTitle()
    {
        return $this->title;
    }

    public function getSearchBody()
    {
        return $this->body;
    }

    public function getSearchUrl()
    {
        return $this->url;
    }
}
```

Usage
-----
See example `Search` module.
