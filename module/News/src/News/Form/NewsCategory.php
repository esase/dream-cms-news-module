<?php
namespace News\Form;

use Application\Form\ApplicationAbstractCustomForm;
use Application\Form\ApplicationCustomFormBuilder;
use News\Model\NewsAdministration as NewsAdministrationModel;

class NewsCategory extends ApplicationAbstractCustomForm 
{
    /**
     * Category name max string length
     */
    const CATEGORY_NAME_MAX_LENGTH = 50;

    /**
     * Form name
     * @var string
     */
    protected $formName = 'news-category';

    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * Category id
     * @var integer
     */
    protected $categoryId;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'name' => [
            'name' => 'name',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Name',
            'required' => true,
            'max_length' => self::CATEGORY_NAME_MAX_LENGTH
        ],
        'submit' => [
            'name' => 'submit',
            'type' => ApplicationCustomFormBuilder::FIELD_SUBMIT,
            'label' => 'Submit'
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
            // add extra validators
            $this->formElements['name']['validators'] = [
                [
                    'name' => 'callback',
                    'options' => [
                        'callback' => [$this, 'validateCategoryName'],
                        'message' => 'Category already exists'
                    ]
                ]
            ];

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                    $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);    
        }

        return $this->form;
    }

    /**
     * Set a model
     *
     * @param object $model
     * @return object fluent interface
     */
    public function setModel(NewsAdministrationModel $model)
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Set a category id
     *
     * @param integer $categoryId
     * @return object fluent interface
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
        return $this;
    }

    /**
     * Validate a category name
     *
     * @param $value
     * @param array $context
     * @return boolean
     */
    public function validateCategoryName($value, array $context = [])
    {
        return $this->model->isCategoryFree($value, $this->categoryId);
    }
}