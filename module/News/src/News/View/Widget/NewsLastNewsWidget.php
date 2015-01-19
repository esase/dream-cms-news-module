<?php
namespace News\View\Widget;

use Acl\Service\Acl as AclService;
use Page\View\Widget\PageAbstractWidget;

class NewsLastNewsWidget extends PageAbstractWidget
{
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
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // TODO: Check categories exsting
        // TODO: Get news date into news wrapper +
        // TODO: Show the - view all news +
        
        // check a permission
        if (AclService::checkPermission('news_view_news', false)) {
            $lastNews = $this->getModel()->getLastNews((int) $this->
                    getWidgetSetting('news_count_last_news'), $this->getWidgetSetting('news_categories_last_news'));

            if (count($lastNews)) {
                return $this->getView()->partial('news/widget/last-news', [
                    'all_news_link' => $this->getWidgetSetting('news_all_link_last_news'),
                    'last_news' => $lastNews
                ]);
            }
        }

        return false;
    }
}