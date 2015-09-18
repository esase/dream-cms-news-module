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
return [
    'News\Module'                                  => __DIR__ . '/Module.php',
    'News\Controller\NewsAdministrationController' => __DIR__ . '/src/News/Controller/NewsAdministrationController.php',
    'News\Event\NewsEvent'                         => __DIR__ . '/src/News/Event/NewsEvent.php',
    'News\Exception\NewsException'                 => __DIR__ . '/src/News/Exception/NewsException.php',
    'News\Form\News'                               => __DIR__ . '/src/News/Form/News.php',
    'News\Form\NewsCategory'                       => __DIR__ . '/src/News/Form/NewsCategory.php',
    'News\Form\NewsFilter'                         => __DIR__ . '/src/News/Form/NewsFilter.php',
    'News\Model\NewsAdministration'                => __DIR__ . '/src/News/Model/NewsAdministration.php',
    'News\Model\NewsBase'                          => __DIR__ . '/src/News/Model/NewsBase.php',
    'News\Model\NewsWidget'                        => __DIR__ . '/src/News/Model/NewsWidget.php',
    'News\PagePrivacy\NewsListPrivacy'             => __DIR__ . '/src/News/PagePrivacy/NewsListPrivacy.php',
    'News\PagePrivacy\NewsViewPrivacy'             => __DIR__ . '/src/News/PagePrivacy/NewsViewPrivacy.php',
    'News\PageProvider\NewsPageProvider'           => __DIR__ . '/src/News/PageProvider/NewsPageProvider.php',
    'News\Service\News'                            => __DIR__ . '/src/News/Service/News.php',
    'News\View\Helper\NewsImageUrl'                => __DIR__ . '/src/News/View/Helper/NewsImageUrl.php',
    'News\View\Widget\NewsAbstractWidget'          => __DIR__ . '/src/News/View/Widget/NewsAbstractWidget.php',
    'News\View\Widget\NewsCalendarWidget'          => __DIR__ . '/src/News/View/Widget/NewsCalendarWidget.php',
    'News\View\Widget\NewsCategoriesWidget'        => __DIR__ . '/src/News/View/Widget/NewsCategoriesWidget.php',
    'News\View\Widget\NewsLastNewsWidget'          => __DIR__ . '/src/News/View/Widget/NewsLastNewsWidget.php',
    'News\View\Widget\NewsListWidget'              => __DIR__ . '/src/News/View/Widget/NewsListWidget.php',
    'News\View\Widget\NewsSimilarNewsWidget'       => __DIR__ . '/src/News/View/Widget/NewsSimilarNewsWidget.php',
    'News\View\Widget\NewsViewWidget'              => __DIR__ . '/src/News/View/Widget/NewsViewWidget.php',
    'News\Test\NewsBootstrap'                      => __DIR__ . '/test/Bootstrap.php'
];
