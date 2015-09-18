<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
namespace News\Model;

use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\In as InPredicate;
use Zend\Db\Sql\Expression as Expression;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Predicate\Like as LikePredicate;

class NewsWidget extends NewsBase
{
    /**
     * Seconds in a day
     */
    CONST SECONDS_IN_DAY = 86400;

    /**
     * News categories
     *
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
     *      string title
     * @return \Zend\Paginator\Paginator
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
            list($dateStart, $dateEnd) = $this->getDateRange($filters['date']);

            $select->where->greaterThanOrEqualTo('created', $dateStart);
            $select->where->lessThanOrEqualTo('created', $dateEnd);
        }

        // filter by title
        if (!empty($filters['title'])) {
            $select->where([
                new LikePredicate('a.title', '%' . $filters['title'] . '%')
            ]);
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

    /**
     * Get categories
     *
     * @param string $date
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getCategories($date = null)
    {
        // filter by a created date
        $newsFilter = new Expression('b.news_id = c.id and c.status = ? and c.created <= ?', [
            self::STATUS_APPROVED, time()
        ]);

        if ($date) {
            list($dateStart, $dateEnd) = $this->getDateRange($date);

            $newsFilter = new Expression('b.news_id = c.id and c.status = ? and c.created <= ? and c.created >= ? and c.created <= ?', [
                self::STATUS_APPROVED, time(), $dateStart, $dateEnd
            ]);
        }

        $select = $this->select();
        $select->from(['a' => 'news_category'])
            ->columns([
                'name',
                'slug'
            ])
            ->join(
                ['b' => 'news_category_connection'],
                'a.id = b.category_id',
                [],
                'left'
            )
            ->join(
                ['c' => 'news_list'],
                $newsFilter,
                [
                    'news_count' => new Expression('COUNT(c.id)')
                ],
                'left'
            )
            ->where([
                'a.language' => $this->getCurrentLanguage()
            ])
            ->group('a.id')
            ->order('a.name');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * Get Calendar news
     *
     * @param  integer $dateStart
     * @param  integer $dateEnd
     * @param  string $category
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getCalendarNews($dateStart, $dateEnd, $category = null)
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
                'news_count' => new Expression('COUNT(a.id)')
            ])
            ->group('news_date')
            ->where([
                'a.language' => $this->getCurrentLanguage(),
                'a.status' => self::STATUS_APPROVED
            ])
            ->where->greaterThanOrEqualTo('created', $dateStart)
            ->where->lessThanOrEqualTo('created', $dateEnd);

        // filter by a category
        if ($category) {
            $select->join(
                ['b' => 'news_category_connection'],
                'a.id = b.news_id',
                []
            )
            ->join(
                ['c' => 'news_category'],
                new Expression('c.id = b.category_id and c.slug = ?', [$category]),
                []
            );
        }

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
            // process categories
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
     * @param integer $page
     * @param integer|array $categories
     * @param integer $limit
     * @return array|\Zend\Paginator\Paginator
     */
    public function getLastNews($page, $categories = null, $limit = null)
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

        if ($limit) {
            $select->limit($limit);
        
            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            return $resultSet->toArray();
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage(null));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
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