<?php
namespace News\Service;

use Application\Service\ApplicationServiceLocator as ServiceLocatorService;

class News
{
    /**
     * News categories
     * @var array
     */
    protected static $newsCategories = null;

    /**
     * Get all news categories
     *
     * @return array
     */
    public static function getAllNewsCategories()
    {
        if (null === self::$newsCategories) {
            self::$newsCategories = ServiceLocatorService::getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsBase')
                ->getAllCategories();
        }

        return self::$newsCategories;
    }
}