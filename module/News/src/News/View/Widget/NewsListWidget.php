<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
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
                'show_thumbnails' => (int) $this->getWidgetSetting('news_thumbnails_list_news'),
                'show_link' => $this->getView()->pageUrl('news', [], null, true)
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
            'base_filters' => $baseFilters
         ]);
    }
}