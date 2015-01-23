<?php
namespace News\View\Widget;

class NewsListWidget extends NewsAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // news filter
        $filters = [
            'category' => $this->getRouteParam('category'),
            'date' => $this->getRouteParam('date'),
        ];

        
        // TODO: Copy data-list.phtml, slide-paginator.phtml, view/help/ApplicationRoute.php into DREAM CMS dev
        
        // get pagination params
        /*$page = $this->getRouteParam('page', 1);
        $perPage = $this->getRouteParam('per_page');
        $orderBy = $this->getRouteParam('order_by');
        $orderType = $this->getRouteParam('order_type');*/

        $page = $this->getView()->applicationRoute()->getQueryParam('news_list_page', 1);
        $perPage = $this->getView()->applicationRoute()->getQueryParam('news_list_per_page');
        $orderBy = $this->getView()->applicationRoute()->getQueryParam('news_list_order_by', 'created');
        $orderType = $this->getView()->applicationRoute()->getQueryParam('news_list_order_type', 'desc');

        // get data
        $paginator = $this->getModel()->getNewsList($page, $perPage, $orderBy, $orderType, $filters);

        // get data list
        $dataList = $this->getView()->partial('partial/data-list', [
            'ajax' => [
                'wrapper_id' => 'news-list',
                'widget_connection' => $this->widgetConnectionId,
                'widget_position' => $this->widgetPosition
            ],
            'paginator' => $paginator,
            'paginator_order_list_show' => true,
            'paginator_order_list' => [
                'created' => 'Date',
                'title' => 'Title'
            ],
            'paginator_per_page_show' => true,
            'paginator_per_page_query' => 'news_list_per_page',
            'paginator_page_query' => 'news_list_page',
            'paginator_order_by_query' => 'news_list_order_by',
            'paginator_order_type_query' => 'news_list_order_type',
            'unit' => 'news/partial/_news-unit',
            'unit_params' => [
                'show_thumbnails' => true
            ],
            'per_page' => $perPage,
            'order_by' => $orderBy,
            'order_type' => $orderType
        ]);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $dataList;
        }

        return $this->getView()->partial('news/widget/news-list', [
            'data' => $dataList
        ]);
    }
}