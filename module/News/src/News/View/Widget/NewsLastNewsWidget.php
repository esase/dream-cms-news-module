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

use Acl\Service\Acl as AclService;

class NewsLastNewsWidget extends NewsAbstractWidget
{
    /**
     * News categories
     *
     * @var array|integer
     */
    protected $newsCategories;

    /**
     * News count
     *
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
        if (AclService::checkPermission('news_view_news', false) 
                && false !== $this->getView()->pageUrl('news', [], null, true)) {

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
                    'show_thumbnails' => (int) $this->getWidgetSetting('news_thumbnails_last_news'),
                    'show_link' => $this->getView()->pageUrl('news', [], null, true)
                ]
            ]);

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $dataList;
            }

            return $this->getView()->partial('news/widget/news-list', [
                'all_news_link' => (int) $this->getWidgetSetting('news_all_link_last_news') 
                        && false !== $this->getView()->pageUrl('news-list'),

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
                'show_link' => $this->getView()->pageUrl('news', [], null, true),
                'list_news' => $lastNews
            ]);
        }

        return false;
    }
}