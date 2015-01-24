<?php
namespace News\View\Widget;

class NewsCategoriesWidget extends NewsAbstractWidget
{
    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        $dateFilter = $this->isNewsListPage() 
            ? $this->getRouteParam('date') 
            : null;

        $routeParams  = [
            'page_name' => self::NEWS_LIST_PAGE
        ];

        $routeQueries = [];

        // save all router params and queries on the 'news-list' page
        if ($this->isNewsListPage()) {
            $routeParams = array_merge($routeParams, 
                    $this->getView()->applicationRoute()->getAllDefaultRouteParams());

            $routeQueries = $this->getView()->applicationRoute()->getQuery();
        }

        // get categories
        if (null != ($categories = $this->getModel()->getCategories($dateFilter))) {
            return $this->getView()->partial('news/widget/categories', [
                'category' => $this->isNewsListPage() 
                    ? $this->getRouteParam('category') 
                    : null,

                'categories' => $categories,
                'route_params' => $routeParams,
                'route_queries' => $routeQueries
            ]);
        }

        return false;
    }
}