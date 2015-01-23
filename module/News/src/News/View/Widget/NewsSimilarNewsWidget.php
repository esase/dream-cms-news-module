<?php
namespace News\View\Widget;

use Acl\Service\Acl as AclService;

class NewsSimilarNewsWidget extends NewsAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        $newsInfo = $this->getModel()->getNewsInfo($this->getSlug(), true, false, 'slug', true);
        $similarNews = $this->getModel()->getSimilarNews($newsInfo, (int) $this->
                getWidgetSetting('news_count_similar_news'), (int) $this->getWidgetSetting('news_last_days_similar_news'));

        if (count($similarNews)) {
            return $this->getView()->partial('news/widget/small-list-news', [
                'all_news_link' => $this->getWidgetSetting('news_all_link_similar_news'),
                'show_thumbnails' => $this->getWidgetSetting('news_thumbnails_similar_news'),
                'list_news' => $similarNews
            ]);
        }

        return false;
    }
}