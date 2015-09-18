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

class NewsCalendarWidget extends NewsAbstractWidget
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
            $calendar =  $this->getView()->applicationCalendar();

            // set calendar options
            $calendar->setUrl($this->getWidgetConnectionUrl([
                'month',
                'year'
            ]))
            ->setWrapperId('news-calendar');

            if (null !== ($month = $this->getRequest()->getQuery('month', null))) {
                $calendar->setMonth($month);
            }

            if (null !== ($year = $this->getRequest()->getQuery('year', null))) {
                $calendar->setYear($year);
            }

            $categoryFilter = $this->isNewsListPage() 
                ? $this->getRouteParam('category') 
                : null;

            // get calendar news
            if (null != ($news = $this->getModel()->
                    getCalendarNews($calendar->getStartDate(), $calendar->getEndDate(), $categoryFilter))) {

                $calendarLinks = [];
                $pageName = $this->getView()->pageUrl(self::NEWS_LIST_PAGE);

                $routeParams  = [];
                $routeQueries = [];

                // save all router params and queries on the 'news-list' page
                if ($this->isNewsListPage()) {
                    $routeParams = $this->getView()->applicationRoute()->getAllDefaultRouteParams();
                    $routeQueries = $this->getView()->applicationRoute()->getQuery();

                    // remove this widget's specific params from queries
                    $routeQueries = array_merge($routeQueries, [
                        'month' => null,
                        'year' => null,
                        'widget_connection' => null,
                        'widget_position' => null,
                        '_' => null
                    ]);
                }

                // process list of news
                foreach ($news as $newsInfo) {
                    $date = str_replace('-', '/', $newsInfo->news_date);
                    $title = sprintf($this->getView()->
                            translatePlural('count one news', 'count many news', $newsInfo->news_count), $newsInfo->news_count);

                    $calendarLinks[$newsInfo->news_date] = [
                        'title' => $title,
                        'url' => $this->getView()->url('page', array_merge($routeParams, 
                                ['page_name' => $pageName, 'date' => $date]), ['force_canonical' => true, 'query' => $routeQueries])
                    ];
                }

                $calendar->setLinks($calendarLinks);
            }

            if ($this->getRequest()->isXmlHttpRequest()) {
                return $calendar->getCalendar();
            }

            return $this->getView()->partial('news/widget/calendar', [
                'calendar' => $calendar->getCalendar()
            ]);
        }

        return false;
    }
}