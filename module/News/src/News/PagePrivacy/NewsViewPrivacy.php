<?php
namespace News\PagePrivacy;

use Acl\Service\Acl as AclService;
use Application\Utility\ApplicationRouteParam as RouteParamUtility;
use Application\Service\ApplicationServiceLocator as ServiceLocatorService;
use Page\PagePrivacy\PageAbstractPagePrivacy;

class NewsViewPrivacy extends PageAbstractPagePrivacy
{
    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Get model
     */
    protected function getModel()
    {
        if (!$this->model) {
            $this->model = ServiceLocatorService::getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsWidget');
        }

        return $this->model;
    }

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

        // get a news id from the route or params
        if (!$trustedData) {
            $newsId = $this->objectId ?  $this->objectId : RouteParamUtility::getParam('slug', -1);

            // check an existing news
            if (null == ($newsInfo = $this->
                    getModel()->getNewsInfo($newsId, true, false, 'slug', true))) {

                return false;
            }
        }

        return true;
    }
}