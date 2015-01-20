<?php
namespace News\Model;

use News\Exception\NewsException;
use News\Event\NewsEvent;
use Application\Utility\ApplicationImage as ImageUtility;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Application\Utility\ApplicationErrorLogger;
use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Zend\Db\Sql\Predicate\In as InPredicate;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Predicate\Like as LikePredicate;
use Exception;

class NewsAdministration extends NewsBase
{
    /**
     * Set news's status
     *
     * @param integer $newsId
     * @param boolean $approved
     * @return boolean|string
     */
    public function setNewsStatus($newsId, $approved = true)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('news_list')
                ->set([
                    'status' => $approved ? self::STATUS_APPROVED : self::STATUS_DISAPPROVED,
                ])
                ->where([
                    'id' => $newsId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire events
        true === $approved
            ? NewsEvent::fireApproveNewsEvent($newsId)
            : NewsEvent::fireDisapproveNewsEvent($newsId);

        return true;
    }

    /**
     * Edit category
     *
     * @param array $category
     *      string name
     *      string slug optional
     * @param array $categoryInfo
     *      string name
     *      string slug
     * @return boolean|string
     */
    public function editCategory($category, array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            // generate a new slug
            if (empty($categoryInfo['slug'])) {
                $categoryInfo['slug'] = $this->
                        generateSlug($category['id'], $category['name'], 'news_category', 'id', self::NEWS_CATEGORY_SLUG_LENGTH);
            }

            $update = $this->update()
                ->table('news_category')
                ->set($categoryInfo)
                ->where([
                    'id' => $category['id'],
                    'language' => $this->getCurrentLanguage()
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the edit category event
        NewsEvent::fireEditCategoryEvent($category['id']);
        return true;
    }

    /**
     * Delete category
     *
     * @param integer $categoryId
     * @return boolean|string
     */
    public function deleteCategory($categoryId)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('news_category')
                ->where([
                    'id' => $categoryId,
                    'language' => $this->getCurrentLanguage()
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        $result = $result->count() ? true : false;

        if ($result) {
            // fire the delete category event
            NewsEvent::fireDeleteCategoryEvent($categoryId);
        }

        return $result;
    }

    /**
     * Add a new category
     *
     * @param array $categoryInfo
     *      string name
     *      string slug
     * @return boolean|string
     */
    public function addCategory(array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('news_category')
                ->values(array_merge($categoryInfo, [
                    'language' => $this->getCurrentLanguage()
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            // generate a slug automatically
            if (empty($categoryInfo['slug'])) {
                $update = $this->update()
                    ->table('news_category')
                    ->set([
                        'slug' => $this->generateSlug($insertId, $categoryInfo['name'], 'news_category', 'id', self::NEWS_CATEGORY_SLUG_LENGTH)
                    ])
                    ->where([
                        'id' => $insertId
                    ]);

                $statement = $this->prepareStatementForSqlObject($update);
                $statement->execute();
            }

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add news category event
        NewsEvent::fireAddCategoryEvent($insertId);
        return true;
    }

    /**
     * Edit news
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
     * @param array $formData
     *      string title
     *      string intro
     *      string text
     *      string meta_description
     *      string meta_keywords
     * @param array $categories
     * @param array $image
     * @param boolean $approved
     * @param boolean $deleteImage
     * @return boolean|string
     */
    public function editNews($newsInfo, array $formData, $categories = null, array $image = [], $approved = false, $deleteImage = false)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $extraValues = [
               'status' => $approved ? self::STATUS_APPROVED : self::STATUS_DISAPPROVED,
               'date_edited' => date('Y-m-d')
            ];

            // generate a new slug
            if (empty($formData['slug'])) {
                $extraValues['slug'] = $this->
                        generateSlug($newsInfo['id'], $formData['title'], 'news_list', 'id', self::NEWS_SLUG_LENGTH);
            }

            $update = $this->update()
                ->table('news_list')
                ->set(array_merge($formData, $extraValues))
                ->where([
                    'id' => $newsInfo['id']
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            // update categories
            $this->updateCategories($newsInfo['id'], $categories);

            // upload the news's image
            $this->uploadImage($newsInfo['id'], $image, $newsInfo['image'], $deleteImage);

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the edit news event
        NewsEvent::fireEditNewsEvent($newsInfo['id']);
        return true;
    }

    /**
     * Update categories
     *
     * @param integer $newsId
     * @param string|array $categories
     * @return void
     */
    protected function updateCategories($newsId, $categories = null)
    {
        // clear all old news connections
        $delete = $this->delete()
            ->from('news_category_connection')
            ->where([
                'news_id' => $newsId
            ]);

        $statement = $this->prepareStatementForSqlObject($delete);
        $result = $statement->execute();

        // add categories connections
        if ($categories && is_array($categories)) {
            foreach ($categories as $category) {
                $insert = $this->insert()
                    ->into('news_category_connection')
                    ->values([
                        'category_id' => $category,
                        'news_id' => $newsId
                    ]);

                $statement = $this->prepareStatementForSqlObject($insert);
                $statement->execute();
            }
        }
    }

    /**
     * Add a new news
     *
     * @param array $newsInfo
     *      string title
     *      string intro
     *      string text
     *      string meta_description
     *      string meta_keywords
     * @param array $categories
     * @param array $image
     * @param boolean $approved
     * @return boolean|string
     */
    public function addNews(array $newsInfo, $categories = null, array $image = [], $approved = false)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('news_list')
                ->values(array_merge($newsInfo, [
                    'status' => $approved ? self::STATUS_APPROVED : self::STATUS_DISAPPROVED,
                    'created' => time(),
                    'language' => $this->getCurrentLanguage(),
                    'date_edited' => date('Y-m-d')
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            // generate a slug automatically
            if (empty($newsInfo['slug'])) {
                $update = $this->update()
                    ->table('news_list')
                    ->set([
                        'slug' => $this->generateSlug($insertId, $newsInfo['title'], 'news_list', 'id', self::NEWS_SLUG_LENGTH)
                    ])
                    ->where([
                        'id' => $insertId
                    ]);

                $statement = $this->prepareStatementForSqlObject($update);
                $statement->execute();
            }

            // update categories
            $this->updateCategories($insertId, $categories);

            // upload the news's image
            $this->uploadImage($insertId, $image);

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add news event
        NewsEvent::fireAddNewsEvent($insertId);
        return true;
    }

    /**
     * Upload an news image
     *
     * @param integer $newsId
     * @param array $image
     *      string name
     *      string type
     *      string tmp_name
     *      integer error
     *      integer size
     * @param string $oldImage
     * @param boolean $deleteImage
     * @throws News\Exception\NewsException
     * @return void
     */
    protected function uploadImage($newsId, array $image, $oldImage = null, $deleteImage = false)
    {
        // upload the news's image
        if (!empty($image['name'])) {
            // delete an old image
            if ($oldImage) {
                if (true !== ($result = $this->deleteNewsImage($oldImage))) {
                    throw new NewsException('Image deleting failed');
                }
            }

            // upload the image
            if (false === ($imageName =
                    FileSystemUtility::uploadResourceFile($newsId, $image, self::$imagesDir))) {

                throw new NewsException('Image uploading failed');
            }

            // resize the image
            ImageUtility::resizeResourceImage($imageName, self::$imagesDir,
                    (int) SettingService::getSetting('news_thumbnail_width'),
                    (int) SettingService::getSetting('news_thumbnail_height'), self::$thumbnailsDir);

            ImageUtility::resizeResourceImage($imageName, self::$imagesDir,
                    (int) SettingService::getSetting('news_image_width'),
                    (int) SettingService::getSetting('news_image_height'));

            $update = $this->update()
                ->table('news_list')
                ->set([
                    'image' => $imageName
                ])
                ->where([
                    'id' => $newsId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();
        }
        elseif ($deleteImage && $oldImage) {
            // just delete the news's image
            if (true !== ($result = $this->deleteNewsImage($oldImage))) {
                throw new NewsException('Image deleting failed');
            }

            $update = $this->update()
                ->table('news_list')
                ->set([
                    'image' => null
                ])
                ->where([
                    'id' => $newsId
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();
        }
    }

    /**
     * Get categories
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @return object
     */
    public function getCategories($page = 1, $perPage = 0, $orderBy = null, $orderType = null)
    {
        $orderFields = [
            'id',
            'name',
            'slug'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id',
                'name',
                'slug'
            ])
            ->where([
                'language' => $this->getCurrentLanguage()
            ])
            ->order($orderBy . ' ' . $orderType);

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

    /**
     * Get news
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @param array $filters
     *      string title
     *      string status
     *      array categories
     * @return object
     */
    public function getNews($page = 1, $perPage = 0, $orderBy = null, $orderType = null, array $filters = [])
    {
        $orderFields = [
            'id',
            'title',
            'status',
            'created'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from(['a' => 'news_list'])
            ->columns([
                'id',
                'title',
                'status',
                'created'
            ])
            ->where([
                'a.language' => $this->getCurrentLanguage()
            ])
            ->order('a.' . $orderBy . ' ' . $orderType);

        // filter by status
        if (!empty($filters['status'])) {
            $select->where([
                'a.status' => $filters['status']
            ]);
        }

        // filter by title
        if (!empty($filters['title'])) {
            $select->where([
                new LikePredicate('a.title', '%' . $filters['title'] . '%')
            ]);
        }

        // filter by categories
        if (!empty($filters['categories']) && is_array($filters['categories'])) {
            $select->join(
                ['b' => 'news_category_connection'],
                'a.id = b.news_id',
                []
            );

            $select->where([
                new InPredicate('b.category_id', $filters['categories'])
            ]);

            $select->group('a.id');
        }

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }

    /**
     * Is a category free
     *
     * @param string $categoryName
     * @param integer $categoryId
     * @return boolean
     */
    public function isCategoryFree($categoryName, $categoryId = 0)
    {
        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id'
            ])
            ->where([
                'name' => $categoryName,
                'language' => $this->getCurrentLanguage()
            ]);

        if ($categoryId) {
            $select->where([
                new NotInPredicate('id', [$categoryId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }

    /**
     * Is category slug free
     *
     * @param string $slug
     * @param integer $categoryId
     * @return boolean
     */
    public function isCategorySlugFree($slug, $categoryId = 0)
    {
        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id'
            ])
            ->where([
                'slug' => $slug,
                'language' => $this->getCurrentLanguage()
            ]);

        if ($categoryId) {
            $select->where([
                new NotInPredicate('id', [$categoryId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }

    /**
     * Is slug free
     *
     * @param string $slug
     * @param integer $newsId
     * @return boolean
     */
    public function isSlugFree($slug, $newsId = 0)
    {
        $select = $this->select();
        $select->from('news_list')
            ->columns([
                'id'
            ])
            ->where([
                'slug' => $slug,
                'language' => $this->getCurrentLanguage()
            ]);

        if ($newsId) {
            $select->where([
                new NotInPredicate('id', [$newsId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }
}