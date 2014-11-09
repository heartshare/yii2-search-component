<?php
/**
 * @link https://github.com/himiklab/yii2-search-component
 * @copyright Copyright (c) 2014 HimikLab
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace himiklab\search;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8\CaseInsensitive;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Index\Term as IndexTerm;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\Query\Term;
use ZendSearch\Lucene\Search\Query\Wildcard;

/**
 * Yii2 Zend Lucine search component.
 *
 * @see http://framework.zend.com/manual/1.12/en/zend.search.lucene.html
 * @author HimikLab
 * @package himiklab\search
 */
class Search extends Component
{
    /** @var array $models */
    public $models = [];

    /** @var string $indexDirectory alias or directory path */
    public $indexDirectory = '@app/runtime/search';

    /** @var bool $caseSensitivity */
    public $caseSensitivity = false;

    /** @var int $minPrefixLength Minimum term prefix length (number of minimum non-wildcard characters) */
    public $minPrefixLength = 3;

    /** @var int $resultsLimit 0 means no limit */
    public $resultsLimit = 0;

    /** @var \ZendSearch\Lucene\Index $luceneIndex */
    protected $luceneIndex;

    public function __destruct()
    {
        $this->luceneIndex->commit();
    }

    public function init()
    {
        if ($this->caseSensitivity) {
            Analyzer::setDefault(new Utf8());
        } else {
            Analyzer::setDefault(new CaseInsensitive());
        }

        $this->indexDirectory = FileHelper::normalizePath(Yii::getAlias($this->indexDirectory));
        $this->luceneIndex = $this->getLuceneIndex($this->indexDirectory);
    }

    /**
     * Indexing the contents of the specified models.
     * @throws InvalidConfigException
     */
    public function index()
    {
        if ($this->luceneIndex->count() !== 0) {
            $this->luceneIndex = Lucene::create($this->indexDirectory);
        }

        /** @var \yii\db\ActiveRecord $model */
        foreach ($this->models as $model) {
            if (!is_subclass_of($model::className(), __NAMESPACE__ . '\SearchInterface')) {
                throw new InvalidConfigException('The model object must implement `SearchInterface`');
            }
            /** @var SearchInterface $page */
            foreach ($model::find()->all() as $page) {
                $this->luceneIndex->addDocument(
                    $this->createDocument($page->getSearchTitle(), $page->getSearchBody(), $page->getSearchUrl())
                );
            }
        }
    }

    /**
     * Search page for the term in the index.
     * @param string $term
     * @return array ('results' => array \ZendSearch\Lucene\Search\QueryHit, 'query' => string)
     */
    public function find($term)
    {
        Wildcard::setMinPrefixLength($this->minPrefixLength);
        Lucene::setResultSetLimit($this->resultsLimit);

        return ['results' => $this->luceneIndex->find($term), 'query' => $term];
    }

    /**
     * Remove page from the index.
     * @param string $url
     */
    public function delete($url)
    {
        $query = new Term(new IndexTerm($url, 'url'));
        $hits = $this->luceneIndex->find($query);
        foreach ($hits as $hit) {
            $this->luceneIndex->delete($hit);
        }
        $this->luceneIndex->commit();
    }

    /**
     * Add page to the index.
     * @param string $title
     * @param string $body
     * @param string $url
     */
    public function add($title, $body, $url)
    {
        $this->delete($url);

        $this->luceneIndex->addDocument(
            $this->createDocument($title, $body, $url)
        );
    }

    /**
     * Full index optimization.
     * Each index segment is entirely independent portion of data.
     * So indexes containing more segments need more memory and time for searching.
     * Index optimization is a process of merging several segments into a new one.
     * Index optimization works with data streams and doesn't
     * take a lot of memory but does require processor resources and time.
     */
    public function optimize()
    {
        $this->luceneIndex->optimize();
    }

    protected function getLuceneIndex($directory)
    {
        if (file_exists($directory . DIRECTORY_SEPARATOR . 'segments.gen')) {
            return Lucene::open($directory);
        } else {
            return Lucene::create($directory);
        }
    }

    protected function createDocument($title, $body, $url)
    {
        $document = new Document();
        $document->addField(Field::text(
            'title',
            $title
        ));
        $document->addField(Field::text(
            'body',
            strip_tags($body)
        ));
        $document->addField(Field::keyword(
            'url',
            $url
        ));

        return $document;
    }
}
