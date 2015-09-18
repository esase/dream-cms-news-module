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

use Application\Utility\ApplicationSlug as SlugUtility;
use Application\Utility\ApplicationErrorLogger;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Application\Model\ApplicationAbstractBase;
use News\Event\NewsEvent;
use News\Exception\NewsException;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Exception;

class NewsBase extends ApplicationAbstractBase
{
    /**
     * News slug length
     */
    const NEWS_SLUG_LENGTH = 80;

    /**
     * News category slug length
     */
    const NEWS_CATEGORY_SLUG_LENGTH = 80;

    /**
     * Approved status
     */
    const STATUS_APPROVED = 'approved';

    /**
     * Disapproved status
     */
    const STATUS_DISAPPROVED = 'disapproved';

    /**
     * Images directory
     *
     * @var string
     */
    protected static $imagesDir = 'news/';

    /**
     * Thumbnails directory
     *
     * @var string
     */
    protected static $thumbnailsDir = 'news/thumbnail/';

    /**
     * News info
     *
     * @var array
     */
    protected static $newsInfo = [];

    /**
     * All categories
     *
     * @var array
     */
    protected static $allCategories = null;

    /**
     * Get images directory name
     *
     * @return string
     */
    public static function getImagesDir()
    {
        return self::$imagesDir;
    }

    /**
     * Get thumbnails directory name
     *
     * @return string
     */
    public static function getThumbnailsDir()
    {
        return self::$thumbnailsDir;
    }

    /**
     * Delete an news's image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deleteNewsImage($imageName)
    {
        $imageTypes = [
            self::$thumbnailsDir,
            self::$imagesDir
        ];

        // delete images
        foreach ($imageTypes as $path) {
            if (true !== ($result = FileSystemUtility::deleteResourceFile($imageName, $path))) {
                return $result;
            }
        }

        return true; 
    }

    /**
     * Get all categories
     * 
     * @return array
     */
    public function getAllCategories()
    {
        if (null !== self::$allCategories) {
            return self::$allCategories;
        }

        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id',
                'name'
            ])
            ->where([
                'language' => $this->getCurrentLanguage()
            ])
            ->order('name');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        // process categories
        self::$allCategories = [];
        foreach ($resultSet as $category) {
            self::$allCategories[$category->id] = $category->name;
        }

        return self::$allCategories;
    }

    /**
     * Get unused news
     *
     * @param integer $limit
     * @return array
     */
    public function getUnusedNews($limit)
    {
        $select = $this->select();
        $select->from('news_list')
            ->columns([
                'id',
                'title',
                'slug',
                'intro',
                'text',
                'status',
                'image',
                'meta_description',
                'meta_keywords',
                'created',
                'language',
                'date_edited'
            ])
            ->limit($limit)
            ->where->and->isNull('language');

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->toArray();
    }

    /**
     * Get all news
     * 
     * @param string $language
     * @param boolean $active
     * @return \Zend\Db\ResultSet\ResultSet
     */
    public function getAllNews($language = null, $active = false)
    {
        $select = $this->select();
        $select->from('news_list')
            ->columns([
                'id',
                'title',
                'slug',
                'intro',
                'text',
                'status',
                'image',
                'meta_description',
                'meta_keywords',
                'created',
                'language',
                'date_edited'
            ])
            ->where([
                'language' => $language
            ])
            ->order('created desc, id desc');

        if ($active) {
            $select->where([
                'status' => self::STATUS_APPROVED
            ])
            ->where->lessThanOrEqualTo('created', time());
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet;
    }

    /**
     * Delete a news
     *
     * @param array $newsInfo
     *      integer id
     *      string title
     *      string slug
     *      string intro
     *      string text
     *      string status
     *      string image
     *      string meta_description
     *      string meta_keywords
     *      integer created
     *      string language
     *      array categories
     *      string date_edited
     * @throws \News\Exception\NewsException
     * @return boolean|string
     */
    public function deleteNews(array $newsInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('news_list')
                ->where([
                    'id' => $newsInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            // delete an image
            if ($newsInfo['image']) {
                if (true !== ($imageDeleteResult = $this->deleteNewsImage($newsInfo['image']))) {
                    throw new NewsException('Image deleting failed');
                }
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result =  $result->count() ? true : false;

        // fire the delete news event
        if ($result) {
            NewsEvent::fireDeleteNewsEvent($newsInfo['id']);
        }

        return $result;
    }

    /**
     * Get category info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @return array
     */
    public function getCategoryInfo($id, $currentLanguage = true)
    {
        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id',
                'name',
                'slug'
            ])
            ->where([
                'id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'language' => $this->getCurrentLanguage()
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }

    /**
     * Get news info
     *
     * @param integer $id
     * @param boolean $currentLanguage
     * @param boolean $categories
     * @param string $field
     * @param boolean $active
     * @return array
     */
    public function getNewsInfo($id, $currentLanguage = true, $categories = false, $field = 'id', $active = false)
    {
        // memory cache key
        $memoryKey = implode('_', func_get_args());

        // check data in a memory
        if (isset(self::$newsInfo[$memoryKey])) {
            return self::$newsInfo[$memoryKey];
        }

        $select = $this->select();
        $select->from('news_list')
            ->columns([
                'id',
                'title',
                'slug',
                'intro',
                'text',
                'status',
                'image',
                'meta_description',
                'meta_keywords',
                'created',
                'language',
                'date_edited'
            ])
            ->where([
                ($field == 'id' ? $field : 'slug') => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'language' => $this->getCurrentLanguage()
            ]);
        }

        if ($active) {
            $select->where([
                'status' => self::STATUS_APPROVED
            ])
            ->where->lessThanOrEqualTo('created', time());
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        // get categories
        if (null != ($news = $result->current()) && $categories) {
            $select = $this->select();
            $select->from('news_category_connection')
                ->columns([
                    'category_id'
                ])
                ->where([
                    'news_id' => $news['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($select);
            $result = $statement->execute();

            foreach($result as $category) {
                $news['categories'][] = $category['category_id'];
            }
        }

        self::$newsInfo[$memoryKey] = $news;

        return $news;
    }

    /**
     * Generate slug
     *
     * @param integer $objectId
     * @param string $title
     * @param string $table
     * @param string $idField
     * @param integer $slugLength
     * @param array $filters
     * @param string $slugField
     * @param string $spaceDivider
     * @return string
     */
    public function generateSlug($objectId, $title, $table, $idField, $slugLength = 60, array $filters = [], $slugField = 'slug', $spaceDivider = '-')
    {
        // generate a slug
        $newSlug  = $slug = SlugUtility::slugify($title, $slugLength, $spaceDivider);
        $slagSalt = null;

        while (true) {
            // check the slug existent
            $select = $this->select();
            $select->from($table)
                ->columns([
                    $slugField
                ])
                ->where([
                    $slugField => $newSlug,
                    'language' => $this->getCurrentLanguage()                    
                ] + $filters);

            $select->where([
                new NotInPredicate($idField, [$objectId])
            ]);

            $statement = $this->prepareStatementForSqlObject($select);
            $resultSet = new ResultSet;
            $resultSet->initialize($statement->execute());

            // generated slug not found
            if (!$resultSet->current()) {
                break;
            }
            else {
                $newSlug = $objectId . $spaceDivider . $slug . $slagSalt;
            }

            // add an extra slug
            $slagSalt = $spaceDivider . SlugUtility::generateRandomSlug($this->slugLength); // add a salt
        }

        return $newSlug;
    }
}