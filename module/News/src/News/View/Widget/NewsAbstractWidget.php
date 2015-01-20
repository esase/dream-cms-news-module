<?php
namespace News\View\Widget;

use Page\View\Widget\PageAbstractWidget;

abstract class NewsAbstractWidget extends PageAbstractWidget
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
            $this->model = $this->getServiceLocator()
                ->get('Application\Model\ModelManager')
                ->getInstance('News\Model\NewsWidget');
        }

        return $this->model;
    }
}