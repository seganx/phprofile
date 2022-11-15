DROP TABLE IF EXISTS `profile`;
CREATE TABLE `profile` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `username` varchar(64) CHARACTER SET ascii DEFAULT NULL,
  `password` varchar(64) CHARACTER SET ascii DEFAULT NULL,
  `nickname` varchar(64) CHARACTER SET utf8 DEFAULT NULL,
  `status` varchar(128) CHARACTER SET utf8 DEFAULT NULL,
  `avatar` varchar(256) CHARACTER SET ascii DEFAULT NULL,
  INDEX (`device_id`)
) ENGINE=InnoDB AUTO_INCREMENT=404;

DROP TABLE IF EXISTS `profile_data`;
CREATE TABLE `profile_data` (
  `profile_id` int unsigned NOT NULL PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `private_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  `public_data` mediumtext CHARACTER SET ascii DEFAULT NULL,
  INDEX (`device_id`)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `league_name`;
CREATE TABLE `league_total` (
  `profile_id` int unsigned NOT NULL PRIMARY KEY,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `score` int DEFAULT 0,
  `rank` int DEFAULT 0,
  `end_score` int) DEFAULT 0,
  `end_rank` int DEFAULT 0,
  INDEX (`device_id`)
) ENGINE=InnoDB;


DROP TABLE IF EXISTS `friends`;
CREATE TABLE `friends` (
  `id` int unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `profile_id` int unsigned NOT NULL,
  `device_id` varchar(64) CHARACTER SET ascii NOT NULL,
  `friend_id` int unsigned NOT NULL,
  INDEX (`profile_id`),
  INDEX (`friend_id`),
  INDEX (`device_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` varchar(32) NOT NULL PRIMARY KEY,
  `profile_id` int unsigned NOT NULL,
  `asset_id` int unsigned NOT NULL,
  `views` int unsigned NOT NULL,
  `likes` int NOT NULL,
  INDEX (`profile_id`),
  INDEX (`asset_id`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `likes`;
CREATE TABLE `likes` (
  `id` varchar(32) NOT NULL PRIMARY KEY,
  `profile_id` int unsigned NOT NULL,
  `owner_id` int unsigned NOT NULL,
  `asset_id` int unsigned NOT NULL,
  `liked` tinyint unsigned NOT NULL,
  INDEX (`profile_id`),
  INDEX (`owner_id`),
  INDEX (`asset_id`)
) ENGINE=InnoDB;


-- people who have gems but have not purchase
SELECT p.id, s.version, s.gems, p.nickname, p.device_id 
FROM profile p
INNER JOIN stats s ON p.id=s.profile_id 
where s.gems>50000 AND (p.id not in (select profile_id from purchases))
ORDER BY s.gems DESC

-- display duplicated fields in a table
SELECT token, COUNT(token) FROM purchases GROUP BY token HAVING COUNT(token) > 1;

-- remove duplicated rows from a table
DELETE c1 FROM purchases c1
INNER JOIN purchases c2 
WHERE c1.time > c2.time AND c1.token = c2.token;


-- age of stored PROCEDURE:
DROP FUNCTION IF EXISTS assets_update_data;
DELIMITER $$
CREATE FUNCTION assets_update_data(data JSON, j_name CHAR(32), a_view INT, a_like INT) RETURNS JSON DETERMINISTIC
BEGIN
	SET @js = JSON_EXTRACT(data, j_name);
	SET @a0 = JSON_EXTRACT(@js, '$[0]');
    SET @a1 = JSON_EXTRACT(@js, '$[1]');
    SET @a0 = IF(@a0 IS NULL, 0, @a0);
    SET @a1 = IF(@a1 IS NULL, 0, @a1);
	return JSON_SET(data, j_name, JSON_ARRAY(@a0 + a_view, @a1 + a_like));
END;


DROP PROCEDURE IF EXISTS assets_update;
DELIMITER !!
CREATE PROCEDURE assets_update(p_id INT, a_name CHAR(32), a_view INT, a_like INT)
BEGIN
	INSERT INTO `assets` (`profile_id`, `data`) 
    VALUES (p_id, assets_update_data("{}", a_name, a_view, a_like)) 
    ON DUPLICATE KEY UPDATE `data`=assets_update_data(`data`, a_name, a_view, a_like);
END;

DROP PROCEDURE IF EXISTS likes_update;
DELIMITER !!
CREATE PROCEDURE likes_update(id varchar(32), a_name CHAR(32), a_liked INT)
BEGIN
	INSERT INTO `likes` (`id`, `data`) 
    VALUES (id, JSON_SET("{}", a_name, a_liked)) 
    ON DUPLICATE KEY UPDATE `data`=JSON_SET(`data`, a_name, a_liked);
END;

DROP PROCEDURE IF EXISTS social_get_public;
DELIMITER !!
CREATE PROCEDURE social_get_public(o_id INT, p_id INT)
BEGIN
	SELECT profile_data.public_data, assets.data as `assets`, likes.data as `likes` 
    from `profile_data`, `assets`, `likes` 
    WHERE profile_data.profile_id=o_id AND assets.profile_id=o_id AND likes.id=CONCAT(p_id,'_',o_id);
END;

CALL social_get_public(410, 450);
CALL assets_update(410, '$.a200', 1, 0);
CALL likes_update('410_415', '$.a200', 1);
