<?php
namespace News\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\In as InPredicate;

class NewsWidget extends NewsBase
{
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
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->order('a.created desc')
            ->limit($limit);

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