<?php
namespace News\Model;

use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\In as InPredicate;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;

class NewsWidget extends NewsBase
{
    /**
     * Seconds in a day
     */
    CONST SECONDS_IN_DAY = 86400;

    /**
     * News categories
     * @var array
     */
    protected static $newsCategories = [];

    /**
     * Get news list
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @param array $filters
     *      integer category
     *      string date
     * @return object
     */
    public function getNewsList($page = 1, $perPage = 0, $orderBy = null, $orderType = null, array $filters = [])
    {
        $orderFields = [
            'title',
            'created'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'created';

        $select = $this->select();
        $select->from(['a' => 'news_list'])
            ->columns([
                'title',
                'slug',
                'intro',
                'image',
                'created'
            ])
            ->order('a.' . $orderBy . ' ' . $orderType)
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->where->lessThanOrEqualTo('created', time());

        // filter by a category
        if (!empty($filters['category'])) {
            $select->join(
                ['b' => 'news_category_connection'],
                'a.id = b.news_id',
                []
            )
            ->join(
                ['c' => 'news_category'],
                new Expression('c.id = b.category_id and c.slug = ?', [$filters['category']]),
                []
            );
        }

        // filter by a created date
        if (!empty($filters['date'])) {
            $date = strtotime($filters['date']);
            $select->where->greaterThanOrEqualTo('created', $date);
            $select->where->lessThanOrEqualTo('created', $date);
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

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
     * Get similar news
     *
     * @param array $newsInfo
     * @param integer $limit
     * @param integer $lastDays
     * @return array
     */
    public function getSimilarNews($newsInfo, $limit, $lastDays)
    {
        $dateEnd = time();
        $dateStart = $dateEnd - self::SECONDS_IN_DAY * $lastDays;

        $select = $this->select();
        $select->from(['a' => 'news_list'])
            ->columns([
                'title',
                'slug',
                'intro',
                'image',
                'created'
            ])
            ->order(new Expression('RAND()'))
            ->limit($limit)
            ->where([
                new NotInPredicate('id', [$newsInfo['id']])
            ])
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->where->greaterThanOrEqualTo('a.created', $dateStart)
            ->where->lessThanOrEqualTo('a.created', $dateEnd);

        // get news categories
        if (null != ($newsCategories = $this->getNewsCategories($newsInfo['id']))) {
            // proccess categories
            $processedCategories = [];

            foreach($newsCategories as $category) {
                $processedCategories[] = $category['category_id'];
            }

            $select->join(
                ['b' => 'news_category_connection'],
                'a.id = b.news_id',
                []
            );

            $select->where([
                new InPredicate('b.category_id', $processedCategories)
            ]);

            $select->group('a.id');
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
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
        if (isset(self::$newsCategories[$newsId])) {
            return self::$newsCategories[$newsId];
        }

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

        self::$newsCategories[$newsId] = $resultSet->toArray();
        return self::$newsCategories[$newsId];
    }
}