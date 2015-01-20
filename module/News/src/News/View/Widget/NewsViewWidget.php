<?php
namespace News\View\Widget;

use Acl\Service\Acl as AclService;

class NewsViewWidget extends NewsAbstractWidget
{
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