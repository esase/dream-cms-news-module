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

            // get calendar news
            if (null != ($news = $this->getModel()->
                    getCalendarNews($calendar->getStartDate(), $calendar->getEndDate()))) {

                $calendarLinks = [];
                $pageName = $this->getView()->pageUrl('news-list');

                foreach ($news as $newsInfo) {
                    $calendarLinks[$newsInfo->news_date] = [
                        'title' => sprintf($this->getView()->
                                translatePlural('count one news', 'count many news', $newsInfo->news_count), $newsInfo->news_count),

                        'url' => $this->getView()->url('page', ['page_name' => $pageName, 
                                'date' => str_replace('-', '/', $newsInfo->news_date)], ['force_canonical' => true])
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