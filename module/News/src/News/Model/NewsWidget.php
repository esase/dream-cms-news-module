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
     * @return object
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
            $select->join(
                ['b' => 'news_category_connection'],
                'a.id = b.news_id',
                []
            );

            if (is_array($categories)) {
                $select->where([
                    new InPredicate('b.category_id', $categories)
                ]);

                $select->group('a.id');
            }
            else {
                $select->where([
                    'b.category_id' => $categories
                ]);
            }
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }
}