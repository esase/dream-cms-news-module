<?php
namespace News\PagePrivacy;

use Acl\Service\Acl as AclService;
use Page\PagePrivacy\PageAbstractPagePrivacy;
use Application\Service\ApplicationServiceLocator as ServiceLocatorService;

class NewsListPrivacy extends PageAbstractPagePrivacy
{
    /**
     * Is allowed view page
     * 
     * @param array $privacyOptions
     * @param boolean $trusted
     * @return boolean
     */
    public function isAllowedViewPage(array $privacyOptions = [], $trustedData = false)
    {
         // check a permission
        if (!AclService::checkPermission('news_view_news', false)) {
            return false;
        }

        return true;
    }
}