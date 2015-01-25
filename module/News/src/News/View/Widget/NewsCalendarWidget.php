<?php
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