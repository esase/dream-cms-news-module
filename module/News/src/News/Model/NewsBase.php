<?php
namespace News\Model;

use Application\Model\ApplicationAbstractBase;
use Zend\Db\ResultSet\ResultSet;

class NewsBase extends ApplicationAbstractBase
{
    /**
     * News slug lengh
     */
    const NEWS_SLUG_LENGTH = 40;

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
     * Delete an news' image
     *
     * @param string $imageName
     * @return boolean
     */
    protected function deletNewsImage($imageName)
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
}