<?php
namespace News\Event;

use User\Service\UserIdentity as UserIdentityService;
use Application\Event\ApplicationAbstractEvent;

class NewsEvent extends ApplicationAbstractEvent
{
    /**
     * Add category event
     */
    const ADD_CATEGORY = 'news_add_category';

    /**
     * Delete category event
     */
    const DELETE_CATEGORY = 'news_delete_category';

    /**
     * Edit category event
     */
    const EDIT_CATEGORY = 'news_edit_category';

    /**
     * Add news event
     */
    const ADD_NEWS = 'news_add';

    /**
     * Edit news event
     */
    const EDIT_NEWS = 'news_edit';

    /**
     * Delete news event
     */
    const DELETE_NEWS = 'news_delete';

    /**
     * Approve news event
     */
    const APPROVE_NEWS = 'news_approve';

    /**
     * Disapprove news event
     */
    const DISAPPROVE_NEWS = 'news_disapprove';

    /**
     * Fire disapprove news event
     *
     * @param integer $newsId
     * @return void
     */
    public static function fireDisapproveNewsEvent($newsId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News disapproved by guest'
            : 'Event - News disapproved by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$newsId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $newsId];

        self::fireEvent(self::DISAPPROVE_NEWS, 
                $newsId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire approve news event
     *
     * @param integer $newsId
     * @return void
     */
    public static function fireApproveNewsEvent($newsId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News approved by guest'
            : 'Event - News approved by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$newsId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $newsId];

        self::fireEvent(self::APPROVE_NEWS, 
                $newsId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire delete news event
     *
     * @param integer $newsId
     * @return void
     */
    public static function fireDeleteNewsEvent($newsId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News deleted by guest'
            : 'Event - News deleted by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$newsId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $newsId];

        self::fireEvent(self::DELETE_NEWS, 
                $newsId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire edit news event
     *
     * @param integer $newsId
     * @return void
     */
    public static function fireEditNewsEvent($newsId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News edited by guest'
            : 'Event - News edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$newsId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $newsId];

        self::fireEvent(self::EDIT_NEWS, 
                $newsId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add news event
     *
     * @param integer $newsId
     * @return void
     */
    public static function fireAddNewsEvent($newsId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News added by guest'
            : 'Event - News added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$newsId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $newsId];

        self::fireEvent(self::ADD_NEWS, 
                $newsId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire edit category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireEditCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News category edited by guest'
            : 'Event - News category edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::EDIT_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire delete category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireDeleteCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News category deleted by guest'
            : 'Event - News category deleted by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::DELETE_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireAddCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - News category added by guest'
            : 'Event - News category added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::ADD_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }
}