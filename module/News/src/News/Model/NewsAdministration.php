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
use Zend\Db\ResultSet\ResultSet;
use Exception;

class NewsAdministration extends NewsBase
{
    /**
     * Edit category
     *
     * @param integer $categoryId
     * @param array $categoryInfo
     *      string name
     * @return boolean|string
     */
    public function editCategory($categoryId, array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('news_category')
                ->set($categoryInfo)
                ->where([
                    'id' => $categoryId,
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
        NewsEvent::fireEditCategoryEvent($categoryId);
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

        // fire the delete category event
        NewsEvent::fireDeleteCategoryEvent($categoryId);
        return $result->count() ? true : false;
    }

    /**
     * Add a new category
     *
     * @param array $categoryInfo
     *      string name
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
     * @return boolean|string
     */
    public function addNews(array $newsInfo, $categories = null, array $image = [])
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('news_list')
                ->values(array_merge($newsInfo, [
                    'status' => (int) SettingService::getSetting('news_auto_confirm') 
                        ? self::STATUS_APPROVED 
                        : self::STATUS_DISAPPROVED,
                    'created' => time(),
                    'language' => $this->getCurrentLanguage()
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

            // add categories connections
            if ($categories && is_array($categories)) {
                foreach ($categories as $category) {
                    $insert = $this->insert()
                        ->into('news_category_connection')
                        ->values([
                            'category_id' => $category,
                            'news_id' => $insertId
                        ]);

                    $statement = $this->prepareStatementForSqlObject($insert);
                    $statement->execute();
                }
            }

            // upload the news' image
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
        // upload the news' image
        if (!empty($image['name'])) {
            // delete an old image
            if ($oldImage) {
                if (true !== ($result = $this->deletNewsImage($oldImage))) {
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
            // just delete the user's avatar
            if (true !== ($result = $this->deletNewsImage($oldImage))) {
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
            'name'
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
                'name'
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
     * @return object
     */
    public function getNews($page = 1, $perPage = 0, $orderBy = null, $orderType = null)
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
        $select->from('news_list')
            ->columns([
                'id',
                'title',
                'status',
                'created'
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
                new NotInPredicate('user_id', [$newsId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }
}