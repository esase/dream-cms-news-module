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
        // base news filter
        $baseFilters = [
            'category' => $this->getRouteParam('category'),
            'date' => $this->getRouteParam('date')
        ];

        // get pagination params
        $page = $this->getRouteParam('page', 1);
        $perPage = $this->getRouteParam('per_page');
        $orderBy = $this->getRouteParam('order_by', 'created');
        $orderType = $this->getRouteParam('order_type', 'desc');

        // get a filter form
        $filterForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\NewsFilter')
            ->setSimpleMode();

        $request = $this->getRequest();
        $filterForm->getForm()->setData($request->getQuery(), false);

        $filterAvailable = (int) $this->getWidgetSetting('news_filter_list_news');

        // validate the filter form
        if ($filterAvailable && $this->getView()->
                applicationRoute()->getQueryParam('form_name') == $filterForm->getFormName()) {

            // check the filter form validation
            if ($filterForm->getForm()->isValid()) {
                $baseFilters = array_merge($filterForm->getForm()->getData(), $baseFilters);
            }
        }

        // get data
        $paginator = $this->getModel()->getNewsList($page, $perPage, $orderBy, $orderType, $baseFilters);

        // get data list
        $dataList = $this->getView()->partial('partial/data-list', [
            'filter_form' => $filterAvailable ? $filterForm->getForm() : false,
            'ajax' => [
                'wrapper_id' => 'news-list',
                'widget_connection' => $this->widgetConnectionId,
                'widget_position' => $this->widgetPosition
            ],
            'paginator' => $paginator,
            'paginator_order_list_show' => (int) $this->getWidgetSetting('news_sorting_menu_list_news'),
            'paginator_order_list' => [
                'created' => 'Date',
                'title' => 'Title'
            ],
            'paginator_per_page_show' => (int) $this->getWidgetSetting('news_perpage_menu_list_news'),
            'unit' => 'news/partial/_news-unit',
            'unit_params' => [
                'show_thumbnails' => (int) $this->getWidgetSetting('news_thumbnails_list_news')
            ],
            'per_page' => $perPage,
            'order_by' => $orderBy,
            'order_type' => $orderType
        ]);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $dataList;
        }

        return $this->getView()->partial('news/widget/news-list', [
            'news_wrapper' => 'news-list',
            'data' => $dataList,
            'base_filters' => $baseFilters,
            'news_count' => $paginator->count()
        ]);
    }
}