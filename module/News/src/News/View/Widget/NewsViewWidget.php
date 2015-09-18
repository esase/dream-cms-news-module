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

class NewsViewWidget extends NewsAbstractWidget
{
    /**
     * Include js and css files
     *
     * @return void
     */
    public function includeJsCssFiles()
    {
        $this->getView()->layoutHeadLink()->appendStylesheet($this->getView()->layoutAsset('main.css', 'css', 'news'));

        if (!$this->getView()->localization()->isCurrentLanguageLtr()) {
            $this->getView()->layoutHeadLink()->appendStylesheet($this->getView()->layoutAsset('main.rtl.css', 'css', 'news'));
        }
    }

    /**
     * Get widget content
     *
     * @return string|boolean
     */
    public function getContent() 
    {
        // check a permission
        if (AclService::checkPermission('news_view_news')) {
            //  get a news info
            if (null != ($newsInfo = $this->
                    getModel()->getNewsInfo($this->getSlug(), true, false, 'slug', true))) {

                // set breadcrumb and default metas
                $this->getView()->pageBreadcrumb()->setCurrentPageTitle($newsInfo['title']);
                $this->getView()->layout()->setVariables([
                    'defaultMetaDescription' => $newsInfo['title'],
                    'defaultMetaKeywords'  => $newsInfo['title'],
                ]);

                // set meta keywords
                if ($newsInfo['meta_keywords']) {
                    $this->getView()->headMeta()->setName('keywords', $newsInfo['meta_keywords']);
                }

                // set meta description
                if ($newsInfo['meta_description']) {
                    $this->getView()->headMeta()->setName('description', $newsInfo['meta_description']);
                }

                return $this->getView()->partial('news/widget/news-info', [
                    'news' => $newsInfo,
                    'categories' => $this->getModel()->getNewsCategories($newsInfo['id'])
                ]);
            }
        }

        return false;
    }
}