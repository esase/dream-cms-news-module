<?php
namespace News\Form;

use Application\Form\ApplicationCustomFormBuilder;
use Application\Form\ApplicationAbstractCustomForm;
use News\Model\NewsBase as NewsBaseModel;
use News\Service\News as NewsService;

class NewsFilter extends ApplicationAbstractCustomForm 
{
    /**
     * Form name
     * @var string
     */
    protected $formName = 'news-filter';

    /**
     * Form method
     * @var string
     */
    protected $method = 'get';

    /**
     * List of not validated elements
     * @var array
     */
    protected $notValidatedElements = ['submit'];

    /**
     * Simple mode
     * @var boolean
     */
    protected $simpleMode = false;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'title' => [
            'name' => 'title',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Title'
        ],
        'status' => [
            'name' => 'status',
            'type' => ApplicationCustomFormBuilder::FIELD_SELECT,
            'label' => 'Status',
            'values' => [
               NewsBaseModel::STATUS_APPROVED => 'approved',
               NewsBaseModel::STATUS_DISAPPROVED => 'disapproved'
            ]
        ],
        'categories' => [
            'name' => 'categories',
            'type' => ApplicationCustomFormBuilder::FIELD_MULTI_SELECT,
            'label' => 'Categories',
            'values' => []
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Search'
        ]
    ];

    /**
     * Get form instance
     *
     * @return object
     */
    public function getForm()
    {
        // get form builder
        if (!$this->form) {
            if (!$this->simpleMode) {
                // get list news categories
                $categories = NewsService::getAllNewsCategories();
                $this->formElements['categories']['values'] = $categories;

                if (!$categories) {
                    unset($this->formElements['categories']);
                }
            }
            else {
                unset($this->formElements['categories']);
                unset($this->formElements['status']);
            }

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }

    /**
     * Set simple mode
     *
     * @return object NewsFilter
     */
    public function setSimpleMode()
    {
        $this->simpleMode = true;
        return $this;
    }
}