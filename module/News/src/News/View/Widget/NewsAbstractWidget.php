<?php
namespace News\View\Widget;

use Page\View\Widget\PageAbstractWidget;
use Page\Service\Page as PageService;

abstract class NewsAbstractWidget extends PageAbstractWidget
{
    /**
     * News list page
     */
    const NEWS_LIST_PAGE = 'news-list';

    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsWidget');
        }

        return $this->model;
    }

    /**
     * Is news list page
     * 
     * @return boolean
     */
    protected function isNewsListPage()
    {
        return self::NEWS_LIST_PAGE == PageService::getCurrentPage()['slug'];
    }
}