<?php
namespace News\Model;

use Application\Utility\ApplicationFileSystem as FileSystemUtility;
use Application\Model\ApplicationAbstractBase;
use Zend\Db\ResultSet\ResultSet;

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