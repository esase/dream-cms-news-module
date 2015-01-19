<?php
namespace News\Model;

use Application\Utility\ApplicationErrorLogger;
use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Application\Model\ApplicationAbstractBase;
use News\Event\NewsEvent;
use News\Exception\NewsException;
use Zend\Db\ResultSet\ResultSet;
use Exception;

class NewsBase extends ApplicationAbstractBase
{
    /**
     * News slug lengh
     */
    const NEWS_SLUG_LENGTH = 80;

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
     * @var string
     */
    protected static $imagesDir = 'news/';

    /**
     * Thumbnails directory
     * @var string
     */
    protected static $thumbnailsDir = 'news/thumbnail/';

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
        $categories = [];

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
        foreach ($resultSet as $category) {
            $categories[$category->id] = $category->name;
        }

        return $categories;
    }

    /**
     * Get all news
     * 
     * @param string $language
     * @return object ResultSet
     */
    public function getAllNews($language = null)
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
            ]);

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
     * @throws News/Exception/NewsException
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
                'name'
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
     * @return array
     */
    public function getNewsInfo($id, $currentLanguage = true, $categories = false)
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
                'id' => $id
            ]);

        if ($currentLanguage) {
            $select->where([
                'language' => $this->getCurrentLanguage()
            ]);
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

        return $news;
    }
}