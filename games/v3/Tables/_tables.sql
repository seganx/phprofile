SET NAMES utf8mb4;
SET TIME_ZONE = '+00:00';
SET FOREIGN_KEY_CHECKS = 0;

DROP PROCEDURE IF EXISTS `social_get_public`;
DROP PROCEDURE IF EXISTS `social_update_reaction`;
DROP PROCEDURE IF EXISTS `social_asset_stats_apply`;
DROP PROCEDURE IF EXISTS `purchase_record`;
DROP PROCEDURE IF EXISTS `profile_data_create`;
DROP PROCEDURE IF EXISTS `profile_data_set`;
DROP PROCEDURE IF EXISTS `league_total_add_score`;
DROP PROCEDURE IF EXISTS `league_total_create`;
DROP PROCEDURE IF EXISTS `league_total_update`;

DROP FUNCTION IF EXISTS `fix_username`;
DROP FUNCTION IF EXISTS `league_score_add`;
DROP FUNCTION IF EXISTS `league_score_add_total`;
DROP FUNCTION IF EXISTS `social_asset_stats_update_data`;

DROP TABLE IF EXISTS `social_asset_reactions`;
DROP TABLE IF EXISTS `social_asset_stats`;
DROP TABLE IF EXISTS `purchases`;
DROP TABLE IF EXISTS `league_total`;
DROP TABLE IF EXISTS `profile_data`;
DROP TABLE IF EXISTS `profile`;

CREATE TABLE `profile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `device_id` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `join_date` date NOT NULL,
  `join_build` varchar(16) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `client_id` varchar(16) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `username` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `password` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `nickname` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `avatar` varchar(256) CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `device_id` (`device_id`),
  KEY `join_date` (`join_date`),
  KEY `join_build_join_date` (`join_build`, `join_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `profile_data` (
  `profile_id` int unsigned NOT NULL,
  `device_id` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `gems` int NOT NULL DEFAULT 0,
  `golds` int NOT NULL DEFAULT 0,
  `private_data` mediumtext CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  `public_data` mediumtext CHARACTER SET ascii COLLATE ascii_general_ci DEFAULT NULL,
  PRIMARY KEY (`profile_id`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `fk_profile_data_profile`
    FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `league_total` (
  `profile_id` int unsigned NOT NULL,
  `device_id` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `score` int NOT NULL DEFAULT 0,
  `rank` int NOT NULL DEFAULT 0,
  `end_score` int NOT NULL DEFAULT 0,
  `end_rank` int NOT NULL DEFAULT 0,
  `total_score` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`profile_id`),
  KEY `device_id` (`device_id`),
  KEY `score` (`score`),
  KEY `rank` (`rank`),
  KEY `total_score` (`total_score`),
  CONSTRAINT `fk_league_total_profile`
    FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `purchases` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_id` int unsigned NOT NULL,
  `version` varchar(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `market` varchar(16) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `sku` varchar(64) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `price` int unsigned NOT NULL,
  `token` varchar(512) CHARACTER SET ascii COLLATE ascii_general_ci NOT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `profile_id` (`profile_id`),
  KEY `timestamp` (`timestamp`),
  KEY `market` (`market`),
  KEY `sku` (`sku`),
  CONSTRAINT `fk_purchases_profile`
    FOREIGN KEY (`profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `social_asset_stats` (
  `owner_profile_id` int unsigned NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  PRIMARY KEY (`owner_profile_id`),
  CONSTRAINT `fk_social_asset_stats_owner`
    FOREIGN KEY (`owner_profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `social_asset_reactions` (
  `viewer_profile_id` int unsigned NOT NULL,
  `owner_profile_id` int unsigned NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  PRIMARY KEY (`viewer_profile_id`, `owner_profile_id`),
  KEY `owner_profile_id` (`owner_profile_id`),
  CONSTRAINT `fk_social_asset_reactions_viewer`
    FOREIGN KEY (`viewer_profile_id`) REFERENCES `profile` (`id`),
  CONSTRAINT `fk_social_asset_reactions_owner`
    FOREIGN KEY (`owner_profile_id`) REFERENCES `profile` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;

DELIMITER ;;

CREATE FUNCTION `fix_username`(pid INT) RETURNS char(32) CHARSET utf8mb4
    DETERMINISTIC
BEGIN
    DECLARE res CHAR(32);
    SET res = LOWER(CONV(18000 + pid, 10, 26));
    SET res = REPLACE(res, '0', 'q');
    SET res = REPLACE(res, '1', 'r');
    SET res = REPLACE(res, '2', 's');
    SET res = REPLACE(res, '3', 't');
    SET res = REPLACE(res, '4', 'u');
    SET res = REPLACE(res, '5', 'v');
    SET res = REPLACE(res, '6', 'w');
    SET res = REPLACE(res, '7', 'x');
    SET res = REPLACE(res, '8', 'y');
    SET res = REPLACE(res, '9', 'z');
    RETURN res;
END ;;

CREATE FUNCTION `league_score_add`(db_score INT, client_score INT, client_value INT) RETURNS int
    DETERMINISTIC
BEGIN
    RETURN IF(client_value <= 0 OR db_score <> client_score, db_score, db_score + client_value);
END ;;

CREATE FUNCTION `league_score_add_total`(db_score INT, client_score INT, db_total_score INT, client_value INT) RETURNS int
    DETERMINISTIC
BEGIN
    RETURN IF(client_value <= 0 OR db_score <> client_score, db_total_score, db_total_score + client_value);
END ;;

CREATE FUNCTION `social_asset_stats_update_data`(stats_data JSON, asset_path VARCHAR(32), view_delta INT, like_delta INT)
RETURNS longtext CHARSET utf8mb4 COLLATE utf8mb4_bin
    DETERMINISTIC
BEGIN
    DECLARE asset_data LONGTEXT DEFAULT NULL;
    DECLARE current_views INT DEFAULT 0;
    DECLARE current_likes INT DEFAULT 0;

    SET asset_data = JSON_EXTRACT(stats_data, asset_path);
    SET current_views = IFNULL(JSON_UNQUOTE(JSON_EXTRACT(asset_data, '$[0]')) + 0, 0);
    SET current_likes = IFNULL(JSON_UNQUOTE(JSON_EXTRACT(asset_data, '$[1]')) + 0, 0);

    RETURN JSON_SET(
        stats_data,
        asset_path,
        JSON_ARRAY(GREATEST(0, current_views + view_delta), GREATEST(0, current_likes + like_delta))
    );
END ;;

CREATE PROCEDURE `profile_data_create`(p_id INT UNSIGNED, p_device_id VARCHAR(64))
BEGIN
    INSERT INTO `profile_data` (`profile_id`, `device_id`)
    SELECT p_id, p_device_id
    WHERE EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_id AND `device_id` = p_device_id)
    ON DUPLICATE KEY UPDATE `device_id` = VALUES(`device_id`);
END ;;

CREATE PROCEDURE `profile_data_set`(
    p_id INT UNSIGNED,
    p_device_id VARCHAR(64),
    p_gems INT,
    p_golds INT,
    p_private_data MEDIUMTEXT,
    p_public_data MEDIUMTEXT,
    p_set_private TINYINT,
    p_set_public TINYINT
)
BEGIN
    IF EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_id AND `device_id` = p_device_id) THEN
        INSERT INTO `profile_data` (`profile_id`, `device_id`, `gems`, `golds`, `private_data`, `public_data`)
        VALUES (
            p_id,
            p_device_id,
            p_gems,
            p_golds,
            IF(p_set_private = 1, p_private_data, NULL),
            IF(p_set_public = 1, p_public_data, NULL)
        )
        ON DUPLICATE KEY UPDATE
            `device_id` = VALUES(`device_id`),
            `gems` = VALUES(`gems`),
            `golds` = VALUES(`golds`),
            `private_data` = IF(p_set_private = 1, VALUES(`private_data`), `private_data`),
            `public_data` = IF(p_set_public = 1, VALUES(`public_data`), `public_data`);
    END IF;
END ;;

CREATE PROCEDURE `league_total_create`(p_id INT UNSIGNED, p_device_id VARCHAR(64), p_score INT, p_rank INT)
BEGIN
    INSERT INTO `league_total` (`profile_id`, `device_id`, `score`, `rank`)
    SELECT p_id, p_device_id, p_score, p_rank
    WHERE EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_id AND `device_id` = p_device_id)
    ON DUPLICATE KEY UPDATE `profile_id` = `profile_id`;
END ;;

CREATE PROCEDURE `league_total_add_score`(
    p_id INT UNSIGNED,
    p_device_id VARCHAR(64),
    p_current_score INT,
    p_value INT,
    p_base_score INT
)
BEGIN
    IF p_value > 0 AND EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_id AND `device_id` = p_device_id) THEN
        INSERT INTO `league_total` (`profile_id`, `device_id`, `score`, `rank`, `total_score`)
        VALUES (p_id, p_device_id, p_base_score, 0, 0)
        ON DUPLICATE KEY UPDATE `profile_id` = `profile_id`;

        UPDATE `league_total`
        SET
            `total_score` = league_score_add_total(`score`, p_current_score, `total_score`, p_value),
            `score` = league_score_add(`score`, p_current_score, p_value)
        WHERE `profile_id` = p_id AND `device_id` = p_device_id;
    END IF;
END ;;

CREATE PROCEDURE `league_total_update`(p_min_score INT, p_count INT)
BEGIN
    UPDATE `league_total` SET `rank` = 0;

    SET @r = 0;
    UPDATE `league_total`
    SET `rank` = @r := (@r + 1)
    WHERE `score` > p_min_score
    ORDER BY `score` DESC
    LIMIT 100000;

    SELECT p.`username`, p.`nickname`, p.`status`, p.`avatar`, l.`score`, l.`rank`
    FROM `profile` p
    INNER JOIN `league_total` l ON l.`profile_id` = p.`id`
    WHERE l.`score` > p_min_score AND l.`rank` > 0
    ORDER BY l.`rank` ASC
    LIMIT p_count;
END ;;

CREATE PROCEDURE `purchase_record`(
    p_profile_id INT UNSIGNED,
    p_version VARCHAR(16),
    p_market VARCHAR(16),
    p_sku VARCHAR(64),
    p_price INT UNSIGNED,
    p_token VARCHAR(512),
    p_status TINYINT UNSIGNED
)
BEGIN
    INSERT INTO `purchases` (`profile_id`, `version`, `market`, `sku`, `price`, `token`, `status`)
    SELECT p_profile_id, p_version, p_market, p_sku, p_price, p_token, p_status
    WHERE EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_profile_id)
    ON DUPLICATE KEY UPDATE
        `version` = IF(`profile_id` = VALUES(`profile_id`), VALUES(`version`), `version`),
        `market` = IF(`profile_id` = VALUES(`profile_id`), VALUES(`market`), `market`),
        `sku` = IF(`profile_id` = VALUES(`profile_id`) AND `status` = 0, VALUES(`sku`), `sku`),
        `price` = IF(`profile_id` = VALUES(`profile_id`) AND VALUES(`status`) >= `status`, VALUES(`price`), `price`),
        `status` = IF(`profile_id` = VALUES(`profile_id`), GREATEST(`status`, VALUES(`status`)), `status`);
END ;;

CREATE PROCEDURE `social_asset_stats_apply`(
    p_owner_id INT UNSIGNED,
    p_asset_path VARCHAR(32),
    p_view_delta INT,
    p_like_delta INT
)
BEGIN
    IF EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_owner_id) THEN
        INSERT INTO `social_asset_stats` (`owner_profile_id`, `data`)
        VALUES (p_owner_id, social_asset_stats_update_data('{}', p_asset_path, p_view_delta, p_like_delta))
        ON DUPLICATE KEY UPDATE
            `data` = social_asset_stats_update_data(`data`, p_asset_path, p_view_delta, p_like_delta);
    END IF;
END ;;

CREATE PROCEDURE `social_update_reaction`(
    p_viewer_id INT UNSIGNED,
    p_owner_id INT UNSIGNED,
    p_asset_path VARCHAR(32),
    p_view INT,
    p_like INT
)
BEGIN
    DECLARE reaction_data LONGTEXT DEFAULT NULL;
    DECLARE asset_reaction LONGTEXT DEFAULT NULL;
    DECLARE old_viewed INT DEFAULT 0;
    DECLARE old_liked INT DEFAULT 0;
    DECLARE new_viewed INT DEFAULT 0;
    DECLARE new_liked INT DEFAULT 0;
    DECLARE view_delta INT DEFAULT 0;
    DECLARE like_delta INT DEFAULT 0;

    IF p_viewer_id <> p_owner_id
        AND EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_viewer_id)
        AND EXISTS (SELECT 1 FROM `profile` WHERE `id` = p_owner_id)
    THEN
        SET reaction_data = (
            SELECT `data`
            FROM `social_asset_reactions`
            WHERE `viewer_profile_id` = p_viewer_id AND `owner_profile_id` = p_owner_id
            LIMIT 1
        );

        IF reaction_data IS NOT NULL THEN
            SET asset_reaction = JSON_EXTRACT(reaction_data, p_asset_path);
            SET old_viewed = IFNULL(JSON_UNQUOTE(JSON_EXTRACT(asset_reaction, '$[0]')) + 0, 0);
            SET old_liked = IFNULL(JSON_UNQUOTE(JSON_EXTRACT(asset_reaction, '$[1]')) + 0, 0);
        END IF;

        SET new_viewed = IF(p_view > 0, 1, old_viewed);
        SET new_liked = IF(p_like > 0, 1, IF(p_like < 0, 0, old_liked));
        SET view_delta = new_viewed - old_viewed;
        SET like_delta = new_liked - old_liked;

        IF view_delta <> 0 OR like_delta <> 0 THEN
            CALL `social_asset_stats_apply`(p_owner_id, p_asset_path, view_delta, like_delta);
        END IF;

        IF p_view > 0 OR p_like <> 0 THEN
            INSERT INTO `social_asset_reactions` (`viewer_profile_id`, `owner_profile_id`, `data`)
            VALUES (p_viewer_id, p_owner_id, JSON_SET('{}', p_asset_path, JSON_ARRAY(new_viewed, new_liked)))
            ON DUPLICATE KEY UPDATE
                `data` = JSON_SET(`data`, p_asset_path, JSON_ARRAY(new_viewed, new_liked));
        END IF;
    END IF;
END ;;

CREATE PROCEDURE `social_get_public`(p_owner_id INT UNSIGNED, p_viewer_id INT UNSIGNED)
BEGIN
    SELECT
        pd.`public_data`,
        stats.`data` AS `assets`,
        reactions.`data` AS `likes`
    FROM `profile` p
    LEFT JOIN `profile_data` pd ON pd.`profile_id` = p.`id`
    LEFT JOIN `social_asset_stats` stats ON stats.`owner_profile_id` = p.`id`
    LEFT JOIN `social_asset_reactions` reactions
        ON reactions.`viewer_profile_id` = p_viewer_id
        AND reactions.`owner_profile_id` = p_owner_id
    WHERE p.`id` = p_owner_id;
END ;;

DELIMITER ;
