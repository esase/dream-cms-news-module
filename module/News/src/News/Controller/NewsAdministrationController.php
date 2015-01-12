<?php
namespace News\Controller;

use Application\Controller\ApplicationAbstractAdministrationController;
use Zend\View\Model\ViewModel;

class NewsAdministrationController extends ApplicationAbstractAdministrationController
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
                ->getInstance('News\Model\NewsAdministration');
        }

        return $this->model;
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        // redirect to the list action
        return $this->redirectTo('news-administration', 'list');
    }

    /**
     * News list 
     */
    public function listAction()
    {
    }

    /**
     * News categories list 
     */
    public function listCategoriesAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        $request = $this->getRequest();

        // get data
        $paginator = $this->getModel()->getCategories($this->getPage(),
                $this->getPerPage(), $this->getOrderBy(), $this->getOrderType());

        return new ViewModel([
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Add a new category action
     */
    public function addCategoryAction()
    {
        // get a category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\NewsCategory')
            ->setModel($this->getModel());

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // add a new category
                if (true === ($result = $this->getModel()->addCategory($categoryForm->getForm()->getData()))) {
                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('news-administration', 'add-category');
            }
        }

        return new ViewModel([
            'category_form' => $categoryForm->getForm()
        ]);
    }

    /**
     * Delete selected categories
     */
    public function deleteCategoriesAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($categoriesIds = $request->getPost('categories', null))) {
                // delete selected categories
                $deleteResult = false;
                $deletedCount = 0;

                foreach ($categoriesIds as $categoryId) {
                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // delete the category
                    if (true !== ($deleteResult = $this->getModel()->deleteCategory($categoryId))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage(($deleteResult ? $this->getTranslator()->translate($deleteResult)
                                : $this->getTranslator()->translate('Error occurred')));

                        break;
                    }

                    $deletedCount++;
                }

                if (true === $deleteResult) {
                    $message = $deletedCount > 1
                        ? 'Selected categories have been deleted'
                        : 'The selected category has been deleted';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('news-administration', 'list-categories', [], true);
    }

    /**
     * Edit a category action
     */
    public function editCategoryAction()
    {
        // get the category info
        if (null == ($category = $this->
                getModel()->getCategoryInfo($this->getSlug()))) {

            return $this->redirectTo('news-administration', 'list-categories');
        }

        // get the category form
        $categoryForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\NewsCategory')
            ->setModel($this->getModel())
            ->setCategoryId($category['id']);

        $categoryForm->getForm()->setData($category);

        $request = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // fill the form with received values
            $categoryForm->getForm()->setData($request->getPost(), false);

            // save data
            if ($categoryForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // edit the category
                if (true === ($result = $this->
                        getModel()->editCategory($category['id'], $categoryForm->getForm()->getData()))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('Category has been edited'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('news-administration', 'edit-category', [
                    'slug' => $category['id']
                ]);
            }
        }

        return new ViewModel([
            'category' => $category,
            'category_form' => $categoryForm->getForm()
        ]);
    }

    /**
     * News settings
     */
    public function settingsAction()
    {
    }
}