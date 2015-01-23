<?php
namespace News\View\Widget;

use Acl\Service\Acl as AclService;

class NewsLastNewsWidget extends NewsAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // check a permission
        if (AclService::checkPermission('news_view_news', false)) {
            $lastNews = $this->getModel()->getLastNews((int) $this->
                    getWidgetSetting('news_count_last_news'), $this->getWidgetSetting('news_categories_last_news'));

            if (count($lastNews)) {
                return $this->getView()->partial('news/widget/small-list-news', [
                    'all_news_link' => $this->getWidgetSetting('news_all_link_last_news'),
                    'show_thumbnails' => $this->getWidgetSetting('news_thumbnails_last_news'),
                    'list_news' => $lastNews
                ]);
            }
        }

        return false;
    }
}