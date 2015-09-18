<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.dream-cms.kg/en/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Dream CMS software.
 * The Initial Developer of the Original Code is Dream CMS (http://www.dream-cms.kg).
 * All portions of the code written by Dream CMS are Copyright (c) 2014. All Rights Reserved.
 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2014 Dream CMS. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Dream CMS software
 * Attribution URL: http://www.dream-cms.kg/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */
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