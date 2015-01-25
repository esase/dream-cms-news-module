<?php
namespace News\PageProvider;

use Application\Service\ApplicationServiceLocator as ServiceLocatorService;
use Page\PageProvider\PageAbstractPageProvider;
use Page\Service\Page as PageService;
use Application\Utility\ApplicationRouteParam as RouteParamUtility;

class NewsPageProvider extends PageAbstractPageProvider
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Pages
     * @var array
     */
    protected static $pages = null;

    /**
     * Dynamic page name
     * @var string
     */
    protected $dynamicPageName = 'news';

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = ServiceLocatorService::getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsBase');
        }

        return $this->model;
    }

    /**
     * Get pages
     *
     * @param string $language
     * @return array
     *      boolean url_active
     *      string url_title
     *      array url_params
     *      array xml_map
     *          string lastmod
     *          string changefreq
     *          string priority
     *     array children
     */
    public function getPages($language)
    {
        if (null === self::$pages) {
            self::$pages = [];
            $news = $this->getModel()->getAllNews($this->getModel()->getCurrentLanguage(), true);
            $currentPage = PageService::getCurrentPage();

            if (count($news)) {
                foreach ($news as $newsInfo) {
                    self::$pages[] = [
                        'url_active' => !empty($currentPage['slug'])
                                && $currentPage['slug'] == $this->dynamicPageName && RouteParamUtility::getParam('slug') == $newsInfo['slug'],

                        'url_title' => $newsInfo['title'],
                        'url_params' => [
                            'slug' => $newsInfo['slug']
                        ],
                        'xml_map' => [
                            'lastmod' => $newsInfo['date_edited'],
                            'changefreq' => null,
                            'priority' => null
                        ],
                        'children' => [
                        ]
                    ];
                }
            }
        }

        return self::$pages;
    }
}