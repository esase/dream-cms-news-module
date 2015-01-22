<?php
namespace News\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\In as InPredicate;
use Zend\Db\Sql\Expression as Expression;

class NewsWidget extends NewsBase
{
    /**
     * Get Calendar news
     *
     * @param  integer $dateStart
     * @param  integer $dateEnd
     * @return object
     */
    public function getCalendarNews($dateStart, $dateEnd)
    {
        $time = time();

        // do not show future news
        if ($dateEnd > $time) {
            $dateEnd = $time;
        }

        $select = $this->select();
        $select->from(['a' => 'news_list'])
            ->columns([
                'news_date' => new Expression('DATE(FROM_UNIXTIME(created))'),
                'news_count' => new Expression('COUNT(id)')
            ])
            ->group('news_date')
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->where->greaterThanOrEqualTo('created', $dateStart)
            ->where->lessThanOrEqualTo('created', $dateEnd);

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * Get last news
     *
     * @param integer $limit
     * @param integer|array $categories
     * @return array
     */
    public function getLastNews($limit, $categories = null)
    {
        $select = $this->select();
        $select->from(['a' => 'news_list'])
            ->columns([
                'title',
                'slug',
                'intro',
                'image',
                'created'
            ])
            ->order('a.created desc, a.id desc')
            ->limit($limit)
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->where->lessThanOrEqualTo('created', time());

        // filter by categories
        if ($categories) {
            // check categories
            if (null != ($existingCategories = $this->getAllCategories())) {
                if (!is_array($categories)) {
                    $categories = [$categories];
                }

                // arrays diff (we can keep deleted categories in settings)
                if (null != ($categories = array_intersect(array_keys($existingCategories), $categories))) {
                    $select->join(
                        ['b' => 'news_category_connection'],
                        'a.id = b.news_id',
                        []
                    );

                    $select->where([
                        new InPredicate('b.category_id', $categories)
                    ]);

                    $select->group('a.id');
                }
            }
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }

    /**
     * Get news categories
     * 
     * @param integer $newsId
     * @return array
     */
    public function getNewsCategories($newsId)
    {
        $select = $this->select();
        $select->from(['a' => 'news_category_connection'])
            ->columns([
                'category_id'
            ])
            ->join(
                ['b' => 'news_category'],
                'a.category_id = b.id',
                [
                    'name',
                    'slug'                    
                ]
            )
            ->where([
                'news_id' => $newsId
            ])
            ->order('b.name');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }
}