<?php
namespace News;

use Application\Service\Application as ApplicationService;
use News\Model\NewsBase as NewsBaseModel;
use Localization\Event\LocalizationEvent;
use Zend\ModuleManager\ModuleManagerInterface;

class Module
{
    /**
     * Init
     */
    public function init(ModuleManagerInterface $moduleManager)
    {
        $eventManager = LocalizationEvent::getEventManager();
        $eventManager->attach(LocalizationEvent::DELETE, function ($e) use ($moduleManager) {
            $news = $moduleManager->getEvent()->getParam('ServiceManager')
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsBase');

            // delete a language dependent news
            if (null != ($newsList = $news->getAllNews($e->getParam('object_id')))) {
                // process news list
                foreach ($newsList as $newsInfo) {
                    $news->deleteNews((array) $newsInfo);
                }
            }
        });
    }

    /**
     * Return autoloader config array
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\ClassMapAutoloader' => [
                __DIR__ . '/autoload_classmap.php',
            ],
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }

    /**
     * Return service config array
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return [];
    }

    /**
     * Init view helpers
     */
    public function getViewHelperConfig()
    {
        return [
            'invokables' => [
                'newsLastNewsWidget' => 'News\View\Widget\NewsLastNewsWidget',
                'newsViewWidget' => 'News\View\Widget\NewsViewWidget',
                'newsCalendarWidget' => 'News\View\Widget\NewsCalendarWidget',
                'newsSimilarNewsWidget' => 'News\View\Widget\NewsSimilarNewsWidget'
            ],
            'factories' => [
                'newsImageUrl' => function(){
                    $thumbDir  = ApplicationService::getResourcesUrl() . NewsBaseModel::getThumbnailsDir();
                    $imageDir = ApplicationService::getResourcesUrl() . NewsBaseModel::getImagesDir();

                    return new \News\View\Helper\NewsImageUrl($thumbDir, $imageDir);
                }
            ]
        ];
    }

    /**
     * Return path to config file
     *
     * @return boolean
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
}