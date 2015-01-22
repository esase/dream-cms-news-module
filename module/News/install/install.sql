SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

SET @moduleId = __module_id__;

-- application admin menu

SET @maxOrder = (SELECT `order` + 1 FROM `application_admin_menu` ORDER BY `order` DESC LIMIT 1);

INSERT INTO `application_admin_menu_category` (`name`, `module`, `icon`) VALUES
('News', @moduleId, 'news_menu_item.png');

SET @menuCategoryId = (SELECT LAST_INSERT_ID());
SET @menuPartId = (SELECT `id` from `application_admin_menu_part` where `name` = 'Modules');

INSERT INTO `application_admin_menu` (`name`, `controller`, `action`, `module`, `order`, `category`, `part`) VALUES
('List of news', 'news-administration', 'list', @moduleId, @maxOrder, @menuCategoryId, @menuPartId),
('List of categories', 'news-administration', 'list-categories', @moduleId, @maxOrder + 1, @menuCategoryId, @menuPartId),
('Settings', 'news-administration', 'settings', @moduleId, @maxOrder + 2, @menuCategoryId, @menuPartId);

-- acl resources

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('news_administration_list', 'ACL - Viewing news in admin area', @moduleId),
('news_administration_list_categories', 'ACL - Viewing news categories in admin area', @moduleId),
('news_administration_settings', 'ACL - Editing news settings in admin area', @moduleId),
('news_administration_add_category', 'ACL - Adding news categories in admin area', @moduleId),
('news_administration_delete_categories', 'ACL - Deleting news categories in admin area', @moduleId),
('news_administration_edit_category', 'ACL - Editing news categories in admin area', @moduleId),
('news_administration_add_news', 'ACL - Adding news in admin area', @moduleId),
('news_administration_edit_news', 'ACL - Editing news in admin area', @moduleId),
('news_administration_delete_news', 'ACL - Deleting news in admin area', @moduleId),
('news_administration_approve_news', 'ACL - Approving news in admin area', @moduleId),
('news_administration_disapprove_news', 'ACL - Disapproving news in admin area', @moduleId);

INSERT INTO `acl_resource` (`resource`, `description`, `module`) VALUES
('news_view_news', 'ACL - Viewing news', @moduleId);
SET @viewNewsResourceId = (SELECT LAST_INSERT_ID());

INSERT INTO `acl_resource_connection` (`role`, `resource`) VALUES
(3, @viewNewsResourceId),
(2, @viewNewsResourceId);

-- application events

INSERT INTO `application_event` (`name`, `module`, `description`) VALUES
('news_add_category', @moduleId, 'Event - Adding news categories'),
('news_delete_category', @moduleId, 'Event - Deleting news categories'),
('news_edit_category', @moduleId, 'Event - Editing news categories'),
('news_add', @moduleId, 'Event - Adding news'),
('news_edit', @moduleId, 'Event - Editing news'),
('news_delete', @moduleId, 'Event - Deleting news'),
('news_approve', @moduleId, 'Event - Approving news'),
('news_disapprove', @moduleId, 'Event - Disapproving news');

-- application settings

INSERT INTO `application_setting_category` (`name`, `module`) VALUES
('Main settings', @moduleId);
SET @settingsCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_auto_approve', 'News auto approve', NULL, 'checkbox', NULL, 1, @settingsCategoryId, @moduleId, 1, NULL, NULL, NULL);
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '1', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_date_format', 'News date format', NULL, 'select', 1, 2, @settingsCategoryId, @moduleId, 1, NULL, NULL, NULL);
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, 'short', NULL);

INSERT INTO `application_setting_predefined_value` (`setting_id`, `value`) VALUES
(@settingId, 'full'),
(@settingId, 'long'),
(@settingId, 'medium'),
(@settingId, 'short');

INSERT INTO `application_setting_category` (`name`, `module`) VALUES
('News images', @moduleId);
SET @settingsCategoryId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_image_width', 'News image width', NULL, 'integer', 1, 3, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '300', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_image_height', 'News image height', NULL, 'integer', 1, 4, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '300', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_thumbnail_width', 'News thumbnail width', NULL, 'integer', 1, 4, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '96', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_thumbnail_height', 'News thumbnail height', NULL, 'integer', 1, 5, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '96', NULL);

-- system pages

INSERT INTO `page_system` (`slug`, `title`, `module`, `disable_menu`, `privacy`, `forced_visibility`, `disable_user_menu`, `disable_site_map`, `disable_footer_menu`, `disable_seo`, `disable_xml_map`, `pages_provider`) VALUES
('news', 'View news', @moduleId, 1, 'News\\PagePrivacy\\NewsViewPrivacy', NULL, 1, NULL, 1, 1, NULL, 'News\\PageProvider\\NewsPageProvider');
SET @newsViewPageId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_system` (`slug`, `title`, `module`, `disable_menu`, `privacy`, `forced_visibility`, `disable_user_menu`, `disable_site_map`, `disable_footer_menu`, `disable_seo`, `disable_xml_map`, `pages_provider`) VALUES
('news-list', 'News list', @moduleId,  NULL, 'News\\PagePrivacy\\NewsListPrivacy', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
SET @newsListPageId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_system_page_depend` (`page_id`, `depend_page_id`) VALUES
(@newsViewPageId, 1),
(@newsListPageId, 1),
(@newsListPageId, @newsViewPageId);

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('newsLastNewsWidget', @moduleId, 'public', 'Last news', 1, NULL, @newsViewPageId);
SET @newsLastNewsWidgetId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('news_count_last_news', @newsLastNewsWidgetId, 'Count of last news', 'integer', 1, 1, 1, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0', NULL);
SET @newsWidgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@newsWidgetSettingId, '5', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('news_categories_last_news', @newsLastNewsWidgetId, 'Categories', 'multiselect', NULL, 2, 1, NULL, NULL, NULL, 'return News\\Service\\News::getAllNewsCategories();');

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('news_all_link_last_news', @newsLastNewsWidgetId, 'Show the link "View all news"', 'checkbox', NULL, 3, 1, NULL, NULL, NULL, NULL);
SET @newsWidgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@newsWidgetSettingId, '1', NULL);

INSERT INTO `page_widget_setting` (`name`, `widget`, `label`, `type`, `required`, `order`, `category`, `description`, `check`,  `check_message`, `values_provider`) VALUES
('news_thumbnails_last_news', @newsLastNewsWidgetId, 'Show news thumbnails', 'checkbox', NULL, 4, 1, NULL, NULL, NULL, NULL);
SET @newsWidgetSettingId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_widget_setting_default_value` (`setting_id`, `value`, `language`) VALUES
(@newsWidgetSettingId, '1', NULL);

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('newsViewWidget', @moduleId, 'public', 'View news', NULL, 1, @newsViewPageId);
SET @newsViewNewsWidgetId = (SELECT LAST_INSERT_ID());

INSERT INTO `page_system_widget_depend` (`page_id`, `widget_id`, `order`) VALUES
(@newsViewPageId,  @newsViewNewsWidgetId,  1);

INSERT INTO `page_widget_page_depend` (`page_id`, `widget_id`) VALUES
(@newsViewPageId,  @newsViewNewsWidgetId);

INSERT INTO `page_widget` (`name`, `module`, `type`, `description`, `duplicate`, `forced_visibility`, `depend_page_id`) VALUES
('newsCalendarWidget', @moduleId, 'public', 'News calendar', NULL, NULL, @newsListPageId);

-- module tables

CREATE TABLE IF NOT EXISTS `news_category` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `language` CHAR(2) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `category` (`name`, `language`),
    UNIQUE `slug` (`slug`, `language`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_list` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `intro` VARCHAR(255) NOT NULL,
    `text` TEXT NOT NULL,
    `status` ENUM('approved','disapproved') NOT NULL,
    `image` VARCHAR(100) DEFAULT NULL,
    `meta_description` VARCHAR(150) DEFAULT NULL,
    `meta_keywords` VARCHAR(150) DEFAULT NULL,
    `created` INT(10) UNSIGNED NOT NULL,
    `language` CHAR(2) NOT NULL,
    `date_edited` DATE NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `slug` (`slug`, `language`),
    KEY `news` (`language`, `status`, `created`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_category_connection` (
    `news_id` INT(11) UNSIGNED NOT NULL,
    `category_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY `news_category` (`news_id`, `category_id`),
    FOREIGN KEY (`news_id`) REFERENCES `news_list`(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `news_category`(`id`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;