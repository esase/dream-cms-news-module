<?php
namespace News\Model;

use News\Event\NewsEvent;
use Application\Utility\ApplicationErrorLogger;
use Application\Service\ApplicationSetting as SettingService;
use Application\Utility\ApplicationPagination as PaginationUtility;
use Zend\Paginator\Adapter\DbSelect as DbSelectPaginator;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Predicate\NotIn as NotInPredicate;
use Zend\Db\ResultSet\ResultSet;
use Exception;

class NewsAdministration extends NewsBase
{
    /**
     * Edit category
     *
     * @param integer $categoryId
     * @param array $categoryInfo
     *      string name
     * @return boolean|string
     */
    public function editCategory($categoryId, array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $update = $this->update()
                ->table('news_category')
                ->set($categoryInfo)
                ->where([
                    'id' => $categoryId,
                    'language' => $this->getCurrentLanguage()
                ]);

            $statement = $this->prepareStatementForSqlObject($update);
            $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the edit category event
        NewsEvent::fireEditCategoryEvent($categoryId);
        return true;
    }

    /**
     * Delete category
     *
     * @param integer $categoryId
     * @return boolean|string
     */
    public function deleteCategory($categoryId)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $delete = $this->delete()
                ->from('news_category')
                ->where([
                    'id' => $categoryId,
                    'language' => $this->getCurrentLanguage()
                ]);

            $statement = $this->prepareStatementForSqlObject($delete);
            $result = $statement->execute();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the delete category event
        NewsEvent::fireDeleteCategoryEvent($categoryId);
        return $result->count() ? true : false;
    }

    /**
     * Add a new category
     *
     * @param array $categoryInfo
     *      string name
     * @return integer|string
     */
    public function addCategory(array $categoryInfo)
    {
        try {
            $this->adapter->getDriver()->getConnection()->beginTransaction();

            $insert = $this->insert()
                ->into('news_category')
                ->values(array_merge($categoryInfo, [
                    'language' => $this->getCurrentLanguage()
                ]));

            $statement = $this->prepareStatementForSqlObject($insert);
            $statement->execute();
            $insertId = $this->adapter->getDriver()->getLastGeneratedValue();

            $this->adapter->getDriver()->getConnection()->commit();
        }
        catch (Exception $e) {
            $this->adapter->getDriver()->getConnection()->rollback();
            ApplicationErrorLogger::log($e);

            return $e->getMessage();
        }

        // fire the add news category event
        NewsEvent::fireAddCategoryEvent($insertId);
        return true;
    }

    /**
     * Is a category free
     *
     * @param string $categoryName
     * @param integer $categoryId
     * @return boolean
     */
    public function isCategoryFree($categoryName, $categoryId = 0)
    {
        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id'
            ])
            ->where([
                'name' => $categoryName,
                'language' => $this->getCurrentLanguage()
            ]);

        if ($categoryId) {
            $select->where([
                new NotInPredicate('id', [$categoryId])
            ]);
        }

        $statement = $this->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet;
        $resultSet->initialize($statement->execute());

        return $resultSet->current() ? false : true;
    }

    /**
     * Get categories
     *
     * @param integer $page
     * @param integer $perPage
     * @param string $orderBy
     * @param string $orderType
     * @return object
     */
    public function getCategories($page = 1, $perPage = 0, $orderBy = null, $orderType = null)
    {
        $orderFields = [
            'id',
            'name'
        ];

        $orderType = !$orderType || $orderType == 'desc'
            ? 'desc'
            : 'asc';

        $orderBy = $orderBy && in_array($orderBy, $orderFields)
            ? $orderBy
            : 'id';

        $select = $this->select();
        $select->from('news_category')
            ->columns([
                'id',
                'name'
            ])
            ->where([
                'language' => $this->getCurrentLanguage()
            ])
            ->order($orderBy . ' ' . $orderType);

        $paginator = new Paginator(new DbSelectPaginator($select, $this->adapter));
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(PaginationUtility::processPerPage($perPage));
        $paginator->setPageRange(SettingService::getSetting('application_page_range'));

        return $paginator;
    }
}