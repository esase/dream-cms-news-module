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
namespace News\PageProvider;

use Application\Service\ApplicationServiceLocator as ServiceLocatorService;
use Page\PageProvider\PageAbstractPageProvider;
use Page\Service\Page as PageService;
use Application\Utility\ApplicationRouteParam as RouteParamUtility;

class NewsPageProvider extends PageAbstractPageProvider
{
    /**
     * Model instance
     *
     * @var \News\Model\NewsBase
     */
    protected $model;

    /**
     * Pages
     *
     * @var array
     */
    protected static $pages = null;

    /**
     * Dynamic page name
     *
     * @var string
     */
    protected $dynamicPageName = 'news';

    /**
     * Get model
     *
     * @return \News\Model\NewsBase
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = ServiceLocatorService::getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsBase');
        }

        return $this->model;
    }

    /**
     * Get pages
     *
     * @param string $language
     * @return array
     *      boolean url_active
     *      string url_title
     *      array url_params
     *      array xml_map
     *          string lastmod
     *          string changefreq
     *          string priority
     *     array children
     */
    public function getPages($language)
    {
        if (null === self::$pages) {
            self::$pages = [];
            $news = $this->getModel()->getAllNews($this->getModel()->getCurrentLanguage(), true);
            $currentPage = PageService::getCurrentPage();

            if (count($news)) {
                foreach ($news as $newsInfo) {
                    self::$pages[] = [
                        'url_active' => !empty($currentPage['slug'])
                                && $currentPage['slug'] == $this->dynamicPageName && RouteParamUtility::getParam('slug') == $newsInfo['slug'],

                        'url_title' => $newsInfo['title'],
                        'url_params' => [
                            'slug' => $newsInfo['slug']
                        ],
                        'xml_map' => [
                            'lastmod' => $newsInfo['date_edited'],
                            'changefreq' => null,
                            'priority' => null
                        ],
                        'children' => [
                        ]
                    ];
                }
            }
        }

        return self::$pages;
    }
}