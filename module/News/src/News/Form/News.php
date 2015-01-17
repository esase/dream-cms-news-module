<?php
namespace News\Form;

use Application\Service\Application as ApplicationService;
use Application\Form\ApplicationAbstractCustomForm;
use Application\Form\ApplicationCustomFormBuilder;
use News\Model\NewsAdministration as NewsAdministrationModel;
use News\Service\News as NewsService;
use News\Model\NewsBase as NewsBaseModel;

class News extends ApplicationAbstractCustomForm 
{
    /**
     * Title max string length
     */
    const TITLE_MAX_LENGTH = 50;

    /**
     * Slug max string length
     */
    const SLUG_MAX_LENGTH = 100;

    /**
     * Intro max string length
     */
    const INTRO_MAX_LENGTH = 255;

    /**
     * Text max string length
     */
    const TEXT_MAX_LENGTH = 65535;

    /**
     * Meta keywords max string length
     */
    const META_KEYWORDS_MAX_LENGTH = 150;

    /**
     * Meta description max string length
     */
    const META_DESCRIPTION_MAX_LENGTH = 150;

    /**
     * Form name
     * @var string
     */
    protected $formName = 'news';

    /**
     * List of ignored elements
     * @var array
     */
    protected $ignoredElements = ['image', 'categories'];

    /**
     * Model instance
     * @var object  
     */
    protected $model;

    /**
     * News id
     * @var integer
     */
    protected $newsId;

    /**
     * News image
     * @var string
     */
    protected $newsImage;

    /**
     * Form elements
     * @var array
     */
    protected $formElements = [
        'title' => [
            'name' => 'title',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Title',
            'required' => true,
            'max_length' => self::TITLE_MAX_LENGTH,
            'category' => 'General info'
        ],
        'slug' => [
            'name' => 'slug',
            'type' => ApplicationCustomFormBuilder::FIELD_SLUG,
            'label' => 'Display name',
            'required' => false,
            'max_length' => self::SLUG_MAX_LENGTH,
            'category' => 'General info',
            'description' => 'The display name will be displayed in the browser bar'
        ],
        'intro' => [
            'name' => 'intro',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Intro',
            'required' => true,
            'max_length' => self::INTRO_MAX_LENGTH,
            'category' => 'General info'
        ],
        'text' => [
            'name' => 'text',
            'type' => ApplicationCustomFormBuilder::FIELD_HTML_AREA,
            'label' => 'Text',
            'required' => true,
            'max_length' => self::TEXT_MAX_LENGTH,
            'category' => 'General info'
        ],
        'categories' => [
            'name' => 'categories',
            'type' => ApplicationCustomFormBuilder::FIELD_MULTI_SELECT,
            'label' => 'Categories',
            'required' => false,
            'category' => 'General info',
        ],
        'image' => [
            'name' => 'image',
            'type' => ApplicationCustomFormBuilder::FIELD_IMAGE,
            'label' => 'Image',
            'required' => false,
            'extra_options' => [
                'file_url' => null,
                'preview' => false,
                'delete_option' => true
            ],
            'category' => 'General info'
        ],
        'meta_keywords' => [
            'name' => 'meta_keywords',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT,
            'label' => 'Meta keywords',
            'required' => false,
            'max_length' => self::META_KEYWORDS_MAX_LENGTH,
            'category' => 'SEO',
            'description' => 'Meta keywords should be separated by comma',
        ],
        'meta_description' => [
            'name' => 'meta_description',
            'type' => ApplicationCustomFormBuilder::FIELD_TEXT_AREA,
            'label' => 'Meta description',
            'required' => false,
            'max_length' => self::META_DESCRIPTION_MAX_LENGTH,
            'category' => 'SEO'
        ],
        'csrf' => [
            'name' => 'csrf',
            'type' => ApplicationCustomFormBuilder::FIELD_CSRF
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
            $this->formElements['slug']['validators'] = [
                [
                    'name' => 'callback',
                    'options' => [
                        'callback' => [$this, 'validateSlug'],
                        'message' => 'Display name already used'
                    ]
                ]
            ];

            // get categories
            $categories = NewsService::getAllNewsCategories();
            $this->formElements['categories']['values'] = $categories;

            if (!$categories) {
                unset($this->formElements['categories']);
            }

            // add preview for the image
            if ($this->newsImage) {
                $this->formElements['image']['extra_options']['preview'] = true;
                $this->formElements['image']['extra_options']['file_url'] =
                        ApplicationService::getResourcesUrl() . NewsBaseModel::getThumbnailsDir() . $this->newsImage;
            }

            $this->form = new ApplicationCustomFormBuilder($this->formName,
                        $this->formElements, $this->translator, $this->ignoredElements, $this->notValidatedElements, $this->method);
        }

        return $this->form;
    }

    /**
     * Set an news image
     *
     * @param string $newsImage
     * @return object fluent interface
     */
    public function setNewsImage($newsImage)
    {
        $this->newsImage = $newsImage;
        return $this;
    }

    /**
     * Validate slug
     *
     * @param $value
     * @param array $context
     * @return boolean
     */
    public function validateSlug($value, array $context = [])
    {
        return $this->model->isSlugFree($value, $this->newsId);
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
     * Set a news id
     *
     * @param integer $newsId
     * @return object fluent interface
     */
    public function setNewsId($newsId)
    {
        $this->newsId = $newsId;
        return $this;
    }
}