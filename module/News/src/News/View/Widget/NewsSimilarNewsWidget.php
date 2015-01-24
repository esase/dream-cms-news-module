<?php
namespace News\View\Widget;

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
            return $this->getView()->partial('news/widget/limited-list-news', [
                'all_news_link' => (int) $this->getWidgetSetting('news_all_link_similar_news'),
                'show_thumbnails' => (int) $this->getWidgetSetting('news_thumbnails_similar_news'),
                'list_news' => $similarNews
            ]);
        }

        return false;
    }
}