<?php
namespace News\Controller;

use Acl\Model\AclBase as AclBaseModel;
use Application\Controller\ApplicationAbstractAdministrationController;
use User\Service\UserIdentity as UserIdentityService;
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
     * Approve selected news
     */
    public function approveNewsAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($newsIds = $request->getPost('news', null))) {
                // approve selected news
                $approveResult = false;
                $approvedCount = 0;

                foreach ($newsIds as $newsId) {
                    // get a news info
                    if (null == ($newsInfo = $this->getModel()->getNewsInfo($newsId))) { 
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // approve the news
                    if (true !== ($approveResult = $this->getModel()->setNewsStatus($newsId))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate($approveResult));

                        break;
                    }

                    $approvedCount++;
                }

                if (true === $approveResult) {
                    $message = $approvedCount > 1
                        ? 'Selected news have been approved'
                        : 'The selected news has been approved';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('news-administration', 'list', [], true);
    }

    /**
     * Disapprove selected news
     */
    public function disapproveNewsAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($newsIds = $request->getPost('news', null))) {
                // disapprove selected news
                $disapproveResult = false;
                $disapprovedCount = 0;

                foreach ($newsIds as $newsId) {
                    // get a news info
                    if (null == ($newsInfo = $this->getModel()->getNewsInfo($newsId))) { 
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // disapprove the news
                    if (true !== ($disapproveResult = $this->getModel()->setNewsStatus($newsId, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate($disapproveResult));

                        break;
                    }

                    $disapprovedCount++;
                }

                if (true === $disapproveResult) {
                    $message = $disapprovedCount > 1
                        ? 'Selected news have been disapproved'
                        : 'The selected news has been disapproved';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('news-administration', 'list', [], true);
    }

    /**
     * News list 
     */
    public function listAction()
    {
        // check the permission and increase permission's actions track
        if (true !== ($result = $this->aclCheckPermission())) {
            return $result;
        }

        $filters = [];

        // get a filter form
        $filterForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\NewsFilter');

        $request = $this->getRequest();
        $filterForm->getForm()->setData($request->getQuery(), false);

        // check the filter form validation
        if ($filterForm->getForm()->isValid()) {
            $filters = $filterForm->getForm()->getData();
        }

        // get data
        $paginator = $this->getModel()->getNews($this->getPage(),
                $this->getPerPage(), $this->getOrderBy(), $this->getOrderType(), $filters);

        return new ViewModel([
            'filter_form' => $filterForm->getForm(),
            'paginator' => $paginator,
            'order_by' => $this->getOrderBy(),
            'order_type' => $this->getOrderType(),
            'per_page' => $this->getPerPage()
        ]);
    }

    /**
     * Add a news action
     */
    public function addNewsAction()
    {
        // get a news form
        $newsForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\News')
            ->setModel($this->getModel());

        $request  = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $newsForm->getForm()->setData($post, false);

            // save data
            if ($newsForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // get news status
                $approved = (int) $this->applicationSetting('news_auto_approve') 
                        || UserIdentityService::getCurrentUserIdentity()['role'] ==  AclBaseModel::DEFAULT_ROLE_ADMIN ? true : false;

                // add a news
                if (true === ($result = $this->getModel()->addNews($newsForm->
                        getForm()->getData(), $this->params()->fromPost('categories'), $this->params()->fromFiles('image'), $approved))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('News has been added'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('news-administration', 'add-news');
            }
        }

        return new ViewModel([
            'news_form' => $newsForm->getForm()
        ]);
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
                        getModel()->editCategory($category, $categoryForm->getForm()->getData()))) {

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
     * Edit a news action
     */
    public function editNewsAction()
    {
        // get the news info
        if (null == ($news = $this->
                getModel()->getNewsInfo($this->getSlug(), true, true))) {

            return $this->redirectTo('news-administration', 'list');
        }

        // get a news form
        $newsForm = $this->getServiceLocator()
            ->get('Application\Form\FormManager')
            ->getInstance('News\Form\News')
            ->setModel($this->getModel())
            ->setNewsId($news['id'])
            ->setNewsImage($news['image']);

        // fill the form with default values
        $newsForm->getForm()->setData($news);
        $request = $this->getRequest();

        // validate the form
        if ($request->isPost()) {
            // make certain to merge the files info!
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );

            // fill the form with received values
            $newsForm->getForm()->setData($post, false);

            // save data
            if ($newsForm->getForm()->isValid()) {
                // check the permission and increase permission's actions track
                if (true !== ($result = $this->aclCheckPermission())) {
                    return $result;
                }

                // get news status
                $approved = (int) $this->applicationSetting('news_auto_approve') 
                        || UserIdentityService::getCurrentUserIdentity()['role'] ==  AclBaseModel::DEFAULT_ROLE_ADMIN ? true : false;

                $deleteImage = (int) $this->getRequest()->getPost('image_delete') ? true : false;

                // edit the news
                if (true === ($result = $this->getModel()->editNews($news, $newsForm->getForm()->getData(), 
                        $this->params()->fromPost('categories'), $this->params()->fromFiles('image'), $approved, $deleteImage))) {

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate('News has been edited'));
                }
                else {
                    $this->flashMessenger()
                        ->setNamespace('error')
                        ->addMessage($this->getTranslator()->translate($result));
                }

                return $this->redirectTo('news-administration', 'edit-news', [
                    'slug' => $news['id']
                ]);
            }
        }

        return new ViewModel([
            'news_form' => $newsForm->getForm(),
            'news' => $news
        ]);
    }

    /**
     * Delete selected news
     */
    public function deleteNewsAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            if (null !== ($newsIds = $request->getPost('news', null))) {
                // delete selected news
                $deleteResult = false;
                $deletedCount = 0;

                foreach ($newsIds as $newsId) {
                    // get news info
                    if (null == ($newsInfo = $this->getModel()->getNewsInfo($newsId))) { 
                        continue;
                    }

                    // check the permission and increase permission's actions track
                    if (true !== ($result = $this->aclCheckPermission(null, true, false))) {
                        $this->flashMessenger()
                            ->setNamespace('error')
                            ->addMessage($this->getTranslator()->translate('Access Denied'));

                        break;
                    }

                    // delete the news
                    if (true !== ($deleteResult = $this->getModel()->deleteNews($newsInfo))) {
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
                        ? 'Selected news have been deleted'
                        : 'The selected news has been deleted';

                    $this->flashMessenger()
                        ->setNamespace('success')
                        ->addMessage($this->getTranslator()->translate($message));
                }
            }
        }

        // redirect back
        return $request->isXmlHttpRequest()
            ? $this->getResponse()
            : $this->redirectTo('news-administration', 'list', [], true);
    }

    /**
     * News settings
     */
    public function settingsAction()
    {
        return new ViewModel([
            'settings_form' => parent::settingsForm('news', 'news-administration', 'settings')
        ]);
    }
}