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