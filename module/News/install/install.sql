SET sql_mode='STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE';

SET @moduleId = __module_id__;
SET @maxOrder = (SELECT `order` + 1 FROM `application_admin_menu` ORDER BY `order` DESC LIMIT 1);

INSERT INTO `application_admin_menu_category` (`name`, `module`, `icon`) VALUES
('News', @moduleId, 'news_menu_item.png');

SET @menuCategoryId = (SELECT LAST_INSERT_ID());
SET @menuPartId = (SELECT `id` from `application_admin_menu_part` where `name` = 'Modules');

INSERT INTO `application_admin_menu` (`name`, `controller`, `action`, `module`, `order`, `category`, `part`) VALUES
('List of news', 'news-administration', 'list', @moduleId, @maxOrder, @menuCategoryId, @menuPartId),
('List of categories', 'news-administration', 'list-categories', @moduleId, @maxOrder + 1, @menuCategoryId, @menuPartId),
('Settings', 'news-administration', 'settings', @moduleId, @maxOrder + 2, @menuCategoryId, @menuPartId);

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

INSERT INTO `application_event` (`name`, `module`, `description`) VALUES
('news_add_category', @moduleId, 'Event - Adding news categories'),
('news_delete_category', @moduleId, 'Event - Deleting news categories'),
('news_edit_category', @moduleId, 'Event - Editing news categories'),
('news_add', @moduleId, 'Event - Adding news'),
('news_edit', @moduleId, 'Event - Editing news'),
('news_delete', @moduleId, 'Event - Deleting news'),
('news_approve', @moduleId, 'Event - Approving news'),
('news_disapprove', @moduleId, 'Event - Disapproving news');

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
(@settingId, '200', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_image_height', 'News image height', NULL, 'integer', 1, 4, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '200', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_thumbnail_width', 'News thumbnail width', NULL, 'integer', 1, 4, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '64', NULL);

INSERT INTO `application_setting` (`name`, `label`, `description`, `type`, `required`, `order`, `category`, `module`, `language_sensitive`, `values_provider`, `check`, `check_message`) VALUES
('news_thumbnail_height', 'News thumbnail height', NULL, 'integer', 1, 5, @settingsCategoryId, @moduleId, NULL, NULL, 'return intval(''__value__'') > 0;', 'Value should be greater than 0');
SET @settingId = (SELECT LAST_INSERT_ID());

INSERT INTO `application_setting_value` (`setting_id`, `value`, `language`) VALUES
(@settingId, '64', NULL);

CREATE TABLE IF NOT EXISTS `news_category` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `language` CHAR(2) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE `category_name` (`name`, `language`),
    FOREIGN KEY (`language`) REFERENCES `localization_list`(`language`)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `news_list` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(50) NOT NULL,
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
    KEY `news_status` (`language`, `status`),
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