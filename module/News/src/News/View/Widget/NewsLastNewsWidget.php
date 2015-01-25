<?php
namespace News\View\Widget;

use Acl\Service\Acl as AclService;

class NewsLastNewsWidget extends NewsAbstractWidget
{
    /**
     * News categories
     * @var array|integer
     */
    protected $newsCategories;

    /**
     * News count
     * @var integer
     */
    protected $newsCount;

    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // check a permission
        if (AclService::checkPermission('news_view_news', false)) {
            // get widget settings
            $this->newsCategories = $this->getWidgetSetting('news_categories_last_news');
            $this->newsCount      = (int) $this->getWidgetSetting('news_count_last_news');

            // select the widget mode
            return !$this->newsCount ? $this->paginationMode() : $this->simpleMode();
        }

        return false;
    }

    /**
     * Pagination mode
     *
     * @return string
     */
    protected function paginationMode()
    {
        // get a pagination page number
        $pageParamName = 'page_' . $this->widgetConnectionId;
        $page = $this->getView()->applicationRoute()->getQueryParam($pageParamName , 1);
        $paginator = $this->getModel()->getLastNews($page, $this->newsCategories);

        if ($paginator->count()) {
            $newsWrapperId = 'last-news-list-' . $this->widgetConnectionId;

            // get data list
            $dataList = $this->getView()->partial('partial/data-list', [
                'ajax' => [
                    'wrapper_id' => $newsWrapperId,
                    'widget_connection' => $this->widgetConnectionId,
                    'widget_position' => $this->widgetPosition
                ],
                'paginator' => $paginator,
                'paginator_page_query' => $pageParamName,
                'unit' => 'news/partial/_news-unit',
                'unit_params' => [
                    'show_thumbnails' => (int) $this->getWidgetSetting('news_thumbnails_last_news')
                ]
            ]);

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $dataList;
            }

            return $this->getView()->partial('news/widget/news-list', [
                'all_news_link' => (int) $this->getWidgetSetting('news_all_link_last_news'),
                'news_wrapper' => $newsWrapperId,
                'data' => $dataList
            ]);
        }

        return false;
    }

    /**
     * Simple mode
     *
     * @return string|boolean
     */
    protected function simpleMode()
    {
        // get limited number of news
        $lastNews = $this->getModel()->getLastNews(null, $this->newsCategories, $this->newsCount);

        if (count($lastNews)) {
            return $this->getView()->partial('news/widget/limited-list-news', [
                'all_news_link' => $this->getWidgetSetting('news_all_link_last_news'),
                'show_thumbnails' => $this->getWidgetSetting('news_thumbnails_last_news'),
                'list_news' => $lastNews
            ]);
        }

        return false;
    }
}